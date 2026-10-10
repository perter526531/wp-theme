<?php
/**
 * Lightweight metabox field engine (self-made, zero dependencies).
 *
 * Usage:
 *   Kabel_Metabox::add( 'product_details', __( 'Product Details', 'kabel' ), 'product', array(...) );
 *
 * Supported field types: text, textarea, select, repeater, media_multi.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Kabel_Metabox
 */
final class Kabel_Metabox {

        /**
         * Registered metabox instances.
         *
         * @var array<string, Kabel_Metabox>
         */
        private static array $instances = array();

        /**
         * Metabox id (also used for nonce keys).
         *
         * @var string
         */
        private string $id;

        /**
         * Meta box title.
         *
         * @var string
         */
        private string $title;

        /**
         * Screen(s) the box appears on.
         *
         * @var string|string[]
         */
        private $screens;

        /**
         * Field definitions.
         *
         * @var array[]
         */
        private array $fields;

        /**
         * Constructor registers the WP hooks.
         */
        private function __construct( string $id, string $title, $screens, array $fields ) {
                $this->id      = $id;
                $this->title   = $title;
                $this->screens = $screens;
                $this->fields  = $fields;

                add_action( 'add_meta_boxes', array( $this, 'register' ) );
                add_action( 'save_post', array( $this, 'save' ), 10, 2 );
        }

        /**
         * Register a metabox.
         */
        public static function add( string $id, string $title, $screens, array $fields ): void {
                self::$instances[ $id ] = new self( $id, $title, $screens, $fields );
        }

        /**
         * Attach the metabox to its screens.
         */
        public function register(): void {
                add_meta_box( 'kabel_mb_' . $this->id, $this->title, array( $this, 'render' ), $this->screens, 'normal', 'high' );
        }

        /**
         * Nonce action name.
         */
        private function nonce_action(): string {
                return 'kabel_mb_' . $this->id;
        }

        /**
         * Render the metabox.
         */
        public function render( WP_Post $post ): void {
                wp_nonce_field( $this->nonce_action(), 'kabel_mb_nonce' );
                echo '<div class="kabel-fields">';
                foreach ( $this->fields as $field ) {
                        $value = get_post_meta( $post->ID, $field['id'], true );
                        $this->render_field( $field, $value );
                }
                echo '</div>';
        }

        /**
         * Render one field.
         */
        private function render_field( array $field, $value ): void {
                $type  = $field['type'] ?? 'text';
                $label = $field['label'] ?? '';
                $desc  = $field['desc'] ?? '';

                echo '<div class="kabel-field kabel-field--' . esc_attr( $type ) . '">';
                printf( '<label class="kabel-field__label" for="%1$s"><strong>%2$s</strong></label>',
                        esc_attr( $field['id'] ),
                        esc_html( $label )
                );
                if ( $desc ) {
                        printf( '<p class="kabel-field__desc">%s</p>', esc_html( $desc ) );
                }

                switch ( $type ) {
                        case 'text':
                                printf(
                                        '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s">',
                                        esc_attr( $field['id'] ),
                                        esc_attr( $value ),
                                        esc_attr( $field['placeholder'] ?? '' )
                                );
                                break;

                        case 'textarea':
                                printf(
                                        '<textarea id="%1$s" name="%1$s" rows="%2$s" class="large-text" placeholder="%3$s">%4$s</textarea>',
                                        esc_attr( $field['id'] ),
                                        (int) ( $field['rows'] ?? 4 ),
                                        esc_attr( $field['placeholder'] ?? '' ),
                                        esc_textarea( (string) $value )
                                );
                                break;

                        case 'select':
                                echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '">';
                                foreach ( (array) ( $field['options'] ?? array() ) as $opt_value => $opt_label ) {
                                        printf(
                                                '<option value="%1$s"%2$s>%3$s</option>',
                                                esc_attr( $opt_value ),
                                                selected( (string) $value, (string) $opt_value, false ),
                                                esc_html( $opt_label )
                                        );
                                }
                                echo '</select>';
                                break;

                        case 'repeater':
                                $this->render_repeater( $field, is_array( $value ) ? $value : array() );
                                break;

                        case 'media_multi':
                                $this->render_media_multi( $field, (string) $value );
                                break;

                        case 'post_multi':
                                $this->render_post_multi( $field, (string) $value );
                                break;
                }

                echo '</div>';
        }

        /**
         * Repeater rows (used for the spec table: group/name/value/unit).
         */
        private function render_repeater( array $field, array $rows ): void {
                $name   = $field['id'];
                $cols   = (array) ( $field['columns'] ?? array() );
                $tpl_id = $name . '_row_template';

                echo '<div class="kabel-repeater" data-kb-repeater="' . esc_attr( $name ) . '">';

                echo '<div class="kabel-repeater__rows">';
                foreach ( $rows as $i => $row ) {
                        $this->render_repeater_row( $name, $cols, is_array( $row ) ? $row : array(), false, (int) $i );
                }
                echo '</div>';

                // Hidden template row cloned by JS when "Add row" is clicked.
                echo '<script type="text/template" id="' . esc_attr( $tpl_id ) . '">';
                $this->render_repeater_row( $name, $cols, array(), true );
                echo '</script>';

                printf(
                        '<button type="button" class="button button-secondary kabel-repeater__add" data-kb-repeater-add="%1$s" data-kb-repeater-tpl="%2$s">%3$s</button>',
                        esc_attr( $name ),
                        esc_attr( $tpl_id ),
                        esc_html( $field['add_label'] ?? __( '+ Add row', 'kabel' ) )
                );
                echo '</div>';
        }

        /**
         * One repeater row (normal or JS template variant).
         */
        private function render_repeater_row( string $name, array $cols, array $row, bool $is_template, int $index = 0 ): void {
                $index = $is_template ? '__INDEX__' : (string) $index;
                echo '<div class="kabel-repeater__row">';
                foreach ( $cols as $col_key => $col_label ) {
                        printf(
                                '<input type="text" name="%1$s[%2$s][%3$s]" value="%4$s" placeholder="%5$s" aria-label="%5$s">',
                                esc_attr( $name ),
                                esc_attr( $index ),
                                esc_attr( $col_key ),
                                esc_attr( (string) ( $row[ $col_key ] ?? '' ) ),
                                esc_attr( $col_label )
                        );
                }
                echo '<button type="button" class="button-link kabel-repeater__remove" data-kb-repeater-remove aria-label="' . esc_attr__( 'Remove row', 'kabel' ) . '">&times;</button>';
                echo '</div>';
        }

        /**
         * Multi-image picker using the native WP media modal.
         */
        private function render_media_multi( array $field, string $value ): void {
                $name = $field['id'];
                $ids  = array_filter( array_map( 'absint', explode( ',', $value ) ) );
                ?>
                <div class="kabel-media-multi" data-kb-media-multi="<?php echo esc_attr( $name ); ?>">
                        <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                        <div class="kabel-media-multi__thumbs">
                                <?php foreach ( $ids as $attachment_id ) : ?>
                                        <?php echo wp_get_attachment_image( $attachment_id, array( 80, 80 ), false, array( 'class' => 'kabel-media-multi__thumb' ) ); ?>
                                <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" data-kb-media-open>
                                <?php esc_html_e( 'Select / reorder images', 'kabel' ); ?>
                        </button>
                </div>
                <?php
        }

        /**
         * Post multi-select (checkbox list of a given post type).
         */
        private function render_post_multi( array $field, string $value ): void {
                $name     = $field['id'];
                $selected = array_filter( array_map( 'absint', explode( ',', $value ) ) );
                $posts    = get_posts( array(
                        'post_type'      => $field['post_type'] ?? 'post',
                        'posts_per_page' => 100,
                        'orderby'        => 'title',
                        'order'          => 'ASC',
                        'post_status'    => 'publish',
                ) );

                if ( empty( $posts ) ) {
                        echo '<p class="kabel-field__desc">' . esc_html__( 'No items available yet.', 'kabel' ) . '</p>';
                        return;
                }

                echo '<ul class="kabel-post-multi">';
                foreach ( $posts as $p ) {
                        printf(
                                '<li><label><input type="checkbox" name="%1$s[]" value="%2$s"%3$s> %4$s</label></li>',
                                esc_attr( $name ),
                                esc_attr( (string) $p->ID ),
                                checked( in_array( (int) $p->ID, $selected, true ), true, false ),
                                esc_html( get_the_title( $p ) )
                        );
                }
                echo '</ul>';
        }

        /**
         * Save handler.
         */
        public function save( int $post_id, WP_Post $post ): void {
                $screen = is_array( $this->screens ) ? $this->screens : array( $this->screens );
                if ( ! in_array( $post->post_type, $screen, true ) ) {
                        return;
                }

                $nonce = $_POST['kabel_mb_nonce'] ?? '';
                if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, $this->nonce_action() ) ) {
                        return;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                        return;
                }
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                        return;
                }

                foreach ( $this->fields as $field ) {
                        $key = $field['id'];
                        $raw = $_POST[ $key ] ?? '';

                        switch ( $field['type'] ?? 'text' ) {
                                case 'repeater':
                                        $clean = array();
                                        if ( is_array( $raw ) ) {
                                                foreach ( $raw as $row ) {
                                                        if ( ! is_array( $row ) ) {
                                                                continue;
                                                        }
                                                        unset( $row['_i'] );
                                                        $row  = array_map( static fn( $v ) => sanitize_text_field( (string) $v ), $row );
                                                        $has  = array_filter( $row );
                                                        if ( $has ) {
                                                                $clean[] = $row;
                                                        }
                                                }
                                        }
                                        update_post_meta( $post_id, $key, $clean );
                                        break;

                                case 'media_multi':
                                        update_post_meta( $post_id, $key, kabel_sanitize_id_list( $raw ) );
                                        break;

                                case 'post_multi':
                                        update_post_meta( $post_id, $key, kabel_sanitize_id_list( $raw ) );
                                        break;

                                case 'textarea':
                                        update_post_meta( $post_id, $key, sanitize_textarea_field( (string) $raw ) );
                                        break;

                                default:
                                        update_post_meta( $post_id, $key, sanitize_text_field( (string) $raw ) );
                        }
                }
        }
}

/* ---------------------------------------------------------------------
 * Metabox definitions — the editing surface for each content type.
 * ------------------------------------------------------------------- */

/**
 * Register all theme metaboxes.
 */
function kabel_register_metaboxes(): void {

        /* Paint series details. */
        Kabel_Metabox::add( 'product_details', __( 'Paint Series Details', 'kabel' ), 'product', array(
                array(
                        'id'    => '_kb_product_model',
                        'type'  => 'text',
                        'label' => __( 'Display name', 'kabel' ),
                        'desc'  => __( 'Optional — shown as the card and page title. Leave empty to use the post title.', 'kabel' ),
                ),
                array(
                        'id'    => '_kb_product_tagline',
                        'type'  => 'text',
                        'label' => __( 'Tagline', 'kabel' ),
                        'desc'  => __( 'One-line positioning statement, e.g. “Luxury velvet matte finish”.', 'kabel' ),
                ),
                array(
                        'id'      => '_kb_product_badge',
                        'type'    => 'select',
                        'label'   => __( 'Corner badge', 'kabel' ),
                        'options' => array(
                                ''    => __( 'None', 'kabel' ),
                                'new' => __( 'NEW', 'kabel' ),
                                'hot' => __( 'HOT', 'kabel' ),
                        ),
                ),
                array(
                        'id'          => '_kb_product_bullets',
                        'type'        => 'textarea',
                        'label'       => __( 'Selling points', 'kabel' ),
                        'desc'        => __( 'One selling point per line.', 'kabel' ),
                        'rows'        => 5,
                        'placeholder' => __( "Velvet-soft matte touch\nScrub-resistant washable film", 'kabel' ),
                ),
                array(
                        'id'       => '_kb_product_specs',
                        'type'     => 'repeater',
                        'label'    => __( 'Specification table', 'kabel' ),
                        'desc'     => __( 'Technical parameters such as binder, dilution, application temperature, recoat interval, coverage, VOC. Rows sharing the same “group” merge into one section on the front end.', 'kabel' ),
                        'columns'  => array(
                                'group' => __( 'Group', 'kabel' ),
                                'name'  => __( 'Parameter', 'kabel' ),
                                'value' => __( 'Value', 'kabel' ),
                                'unit'  => __( 'Unit', 'kabel' ),
                        ),
                        'add_label' => __( '+ Add spec row', 'kabel' ),
                ),
                array(
                        'id'    => '_kb_product_gallery',
                        'type'  => 'media_multi',
                        'label' => __( 'Gallery', 'kabel' ),
                        'desc'  => __( 'Additional effect images shown on the series page (featured image is set on the right).', 'kabel' ),
                ),
        ) );

        /* Project reference details. */
        Kabel_Metabox::add( 'project_details', __( 'Project Details', 'kabel' ), 'projects', array(
                array(
                        'id'    => '_kb_project_location',
                        'type'  => 'text',
                        'label' => __( 'Location', 'kabel' ),
                        'desc'  => __( 'e.g. Singapore — shown as a chip above the title.', 'kabel' ),
                ),
                array(
                        'id'   => '_kb_project_summary',
                        'type' => 'textarea',
                        'label' => __( 'Summary', 'kabel' ),
                        'desc' => __( 'One-line intro shown under the title (optional).', 'kabel' ),
                        'rows' => 3,
                ),
        ) );
}
add_action( 'init', 'kabel_register_metaboxes', 20 );

/**
 * Minimal admin CSS/JS for the field engine (repeater + media picker).
 */
function kabel_metabox_admin_assets( string $hook ): void {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
                return;
        }
        wp_enqueue_style( 'kabel-metabox', KABEL_URI . '/assets/css/admin.css', array(), KABEL_VERSION );
        wp_enqueue_media();
        wp_enqueue_script( 'kabel-metabox', KABEL_URI . '/assets/js/admin.js', array(), KABEL_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'kabel_metabox_admin_assets' );
