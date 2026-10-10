<?php
/**
 * Lightweight metabox field engine (self-made, zero dependencies).
 *
 * Usage:
 *   Dazhen_Metabox::add( 'product_details', __( 'Product Details', 'dazhen' ), 'product', array(...) );
 *
 * Supported field types: text, textarea, select, repeater, media_multi.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Dazhen_Metabox
 */
final class Dazhen_Metabox {

        /**
         * Registered metabox instances.
         *
         * @var array<string, Dazhen_Metabox>
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
                add_meta_box( 'dazhen_mb_' . $this->id, $this->title, array( $this, 'render' ), $this->screens, 'normal', 'high' );
        }

        /**
         * Nonce action name.
         */
        private function nonce_action(): string {
                return 'dazhen_mb_' . $this->id;
        }

        /**
         * Render the metabox.
         */
        public function render( WP_Post $post ): void {
                wp_nonce_field( $this->nonce_action(), 'dazhen_mb_nonce' );
                echo '<div class="dazhen-fields">';
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

                echo '<div class="dazhen-field dazhen-field--' . esc_attr( $type ) . '">';
                printf( '<label class="dazhen-field__label" for="%1$s"><strong>%2$s</strong></label>',
                        esc_attr( $field['id'] ),
                        esc_html( $label )
                );
                if ( $desc ) {
                        printf( '<p class="dazhen-field__desc">%s</p>', esc_html( $desc ) );
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

                echo '<div class="dazhen-repeater" data-dz-repeater="' . esc_attr( $name ) . '">';

                echo '<div class="dazhen-repeater__rows">';
                foreach ( $rows as $i => $row ) {
                        $this->render_repeater_row( $name, $cols, is_array( $row ) ? $row : array(), false, (int) $i );
                }
                echo '</div>';

                // Hidden template row cloned by JS when "Add row" is clicked.
                echo '<script type="text/template" id="' . esc_attr( $tpl_id ) . '">';
                $this->render_repeater_row( $name, $cols, array(), true );
                echo '</script>';

                printf(
                        '<button type="button" class="button button-secondary dazhen-repeater__add" data-dz-repeater-add="%1$s" data-dz-repeater-tpl="%2$s">%3$s</button>',
                        esc_attr( $name ),
                        esc_attr( $tpl_id ),
                        esc_html( $field['add_label'] ?? __( '+ Add row', 'dazhen' ) )
                );
                echo '</div>';
        }

        /**
         * One repeater row (normal or JS template variant).
         */
        private function render_repeater_row( string $name, array $cols, array $row, bool $is_template, int $index = 0 ): void {
                $index = $is_template ? '__INDEX__' : (string) $index;
                echo '<div class="dazhen-repeater__row">';
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
                echo '<button type="button" class="button-link dazhen-repeater__remove" data-dz-repeater-remove aria-label="' . esc_attr__( 'Remove row', 'dazhen' ) . '">&times;</button>';
                echo '</div>';
        }

        /**
         * Multi-image picker using the native WP media modal.
         */
        private function render_media_multi( array $field, string $value ): void {
                $name = $field['id'];
                $ids  = array_filter( array_map( 'absint', explode( ',', $value ) ) );
                ?>
                <div class="dazhen-media-multi" data-dz-media-multi="<?php echo esc_attr( $name ); ?>">
                        <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                        <div class="dazhen-media-multi__thumbs">
                                <?php foreach ( $ids as $attachment_id ) : ?>
                                        <?php echo wp_get_attachment_image( $attachment_id, array( 80, 80 ), false, array( 'class' => 'dazhen-media-multi__thumb' ) ); ?>
                                <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" data-dz-media-open>
                                <?php esc_html_e( 'Select / reorder images', 'dazhen' ); ?>
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
                        echo '<p class="dazhen-field__desc">' . esc_html__( 'No items available yet.', 'dazhen' ) . '</p>';
                        return;
                }

                echo '<ul class="dazhen-post-multi">';
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

                $nonce = $_POST['dazhen_mb_nonce'] ?? '';
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
                                        update_post_meta( $post_id, $key, dazhen_sanitize_id_list( $raw ) );
                                        break;

                                case 'post_multi':
                                        update_post_meta( $post_id, $key, dazhen_sanitize_id_list( $raw ) );
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
function dazhen_register_metaboxes(): void {

        /* Product details. */
        Dazhen_Metabox::add( 'product_details', __( 'Product Details', 'dazhen' ), 'product', array(
                array(
                        'id'    => '_dz_product_model',
                        'type'  => 'text',
                        'label' => __( 'Model number', 'dazhen' ),
                        'desc'  => __( 'e.g. 18E-2 — shown as the card title.', 'dazhen' ),
                ),
                array(
                        'id'    => '_dz_product_tagline',
                        'type'  => 'text',
                        'label' => __( 'Tagline', 'dazhen' ),
                        'desc'  => __( 'One-line positioning statement (shown under the model).', 'dazhen' ),
                ),
                array(
                        'id'      => '_dz_product_badge',
                        'type'    => 'select',
                        'label'   => __( 'Corner badge', 'dazhen' ),
                        'options' => array(
                                ''    => __( 'None', 'dazhen' ),
                                'new' => __( 'NEW', 'dazhen' ),
                                'hot' => __( 'HOT', 'dazhen' ),
                        ),
                ),
                array(
                        'id'          => '_dz_product_bullets',
                        'type'        => 'textarea',
                        'label'       => __( 'Selling points', 'dazhen' ),
                        'desc'        => __( 'One selling point per line.', 'dazhen' ),
                        'rows'        => 5,
                        'placeholder' => __( "3-second instant heat\nTankless design", 'dazhen' ),
                ),
                array(
                        'id'       => '_dz_product_specs',
                        'type'     => 'repeater',
                        'label'    => __( 'Specification table', 'dazhen' ),
                        'desc'     => __( 'Rows sharing the same "group" merge into one section on the front end.', 'dazhen' ),
                        'columns'  => array(
                                'group' => __( 'Group', 'dazhen' ),
                                'name'  => __( 'Parameter', 'dazhen' ),
                                'value' => __( 'Value', 'dazhen' ),
                                'unit'  => __( 'Unit', 'dazhen' ),
                        ),
                        'add_label' => __( '+ Add spec row', 'dazhen' ),
                ),
                array(
                        'id'    => '_dz_product_gallery',
                        'type'  => 'media_multi',
                        'label' => __( 'Gallery', 'dazhen' ),
                        'desc'  => __( 'Additional images shown on the product page (featured image is set on the right).', 'dazhen' ),
                ),
        ) );

        /* Case study details. */
        Dazhen_Metabox::add( 'case_details', __( 'Case Details', 'dazhen' ), 'case_study', array(
                array(
                        'id'    => '_dz_case_country',
                        'type'  => 'text',
                        'label' => __( 'Client country', 'dazhen' ),
                        'desc'  => __( 'e.g. United Kingdom', 'dazhen' ),
                ),
                array(
                        'id'    => '_dz_case_flag',
                        'type'  => 'text',
                        'label' => __( 'Country flag emoji', 'dazhen' ),
                        'desc'  => __( 'e.g. 🇬🇧 (optional)', 'dazhen' ),
                ),
                array(
                        'id'      => '_dz_case_client_type',
                        'type'    => 'select',
                        'label'   => __( 'Client type', 'dazhen' ),
                        'options' => array(
                                'distributor' => __( 'Distributor / Dealer', 'dazhen' ),
                                'hotel'       => __( 'Hotel / Hospitality', 'dazhen' ),
                                'education'   => __( 'Education', 'dazhen' ),
                                'medical'     => __( 'Medical', 'dazhen' ),
                                'office'      => __( 'Office / Commercial', 'dazhen' ),
                                'ecommerce'   => __( 'E-commerce brand', 'dazhen' ),
                        ),
                ),
                array(
                        'id'    => '_dz_case_order',
                        'type'  => 'text',
                        'label' => __( 'Order volume', 'dazhen' ),
                        'desc'  => __( 'e.g. 1,500 units', 'dazhen' ),
                ),
                array(
                        'id'   => '_dz_case_need',
                        'type' => 'textarea',
                        'label' => __( 'Requirement', 'dazhen' ),
                        'rows' => 3,
                ),
                array(
                        'id'   => '_dz_case_challenge',
                        'type' => 'textarea',
                        'label' => __( 'Challenge', 'dazhen' ),
                        'rows' => 3,
                ),
                array(
                        'id'   => '_dz_case_feedback',
                        'type' => 'textarea',
                        'label' => __( 'Client feedback (quote)', 'dazhen' ),
                        'rows' => 3,
                ),
                array(
                        'id'        => '_dz_case_products',
                        'type'      => 'post_multi',
                        'post_type' => 'product',
                        'label'     => __( 'Related products', 'dazhen' ),
                        'desc'      => __( 'Shown as linked product cards on the case page.', 'dazhen' ),
                ),
        ) );

        /* Solution details. */
        Dazhen_Metabox::add( 'solution_details', __( 'Scenario Details', 'dazhen' ), 'solution', array(
                array(
                        'id'      => '_dz_sol_icon',
                        'type'    => 'select',
                        'label'   => __( 'Icon', 'dazhen' ),
                        'options' => array(
                                'home'   => __( 'Home (Household)', 'dazhen' ),
                                'office' => __( 'Office / Commercial', 'dazhen' ),
                                'edu'    => __( 'Education / Medical', 'dazhen' ),
                                'hotel'  => __( 'Hotel / Engineering', 'dazhen' ),
                        ),
                ),
                array(
                        'id'   => '_dz_sol_summary',
                        'type' => 'textarea',
                        'label' => __( 'Card summary', 'dazhen' ),
                        'rows' => 3,
                ),
                array(
                        'id'   => '_dz_sol_pains',
                        'type' => 'textarea',
                        'label' => __( 'Pain points', 'dazhen' ),
                        'desc' => __( 'One pain point per line.', 'dazhen' ),
                        'rows' => 4,
                ),
                array(
                        'id'        => '_dz_sol_products',
                        'type'      => 'post_multi',
                        'post_type' => 'product',
                        'label'     => __( 'Recommended products', 'dazhen' ),
                        'desc'      => __( 'Shown on the scenario landing page.', 'dazhen' ),
                ),
        ) );
}
add_action( 'init', 'dazhen_register_metaboxes', 20 );

/**
 * Minimal admin CSS/JS for the field engine (repeater + media picker).
 */
function dazhen_metabox_admin_assets( string $hook ): void {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
                return;
        }
        wp_enqueue_style( 'dazhen-metabox', DAZHEN_URI . '/assets/css/admin.css', array(), DAZHEN_VERSION );
        wp_enqueue_media();
        wp_enqueue_script( 'dazhen-metabox', DAZHEN_URI . '/assets/js/admin.js', array(), DAZHEN_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'dazhen_metabox_admin_assets' );
