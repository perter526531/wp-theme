import json, re, sys
from html.parser import HTMLParser

class TextExtractor(HTMLParser):
    def __init__(self):
        super().__init__()
        self.skip = 0
        self.parts = []
    def handle_starttag(self, tag, attrs):
        if tag in ('script', 'style'):
            self.skip += 1
        if tag in ('p','div','h1','h2','h3','h4','h5','h6','li','br','tr','section','article','header','footer'):
            self.parts.append('\n')
    def handle_endtag(self, tag):
        if tag in ('script', 'style') and self.skip > 0:
            self.skip -= 1
    def handle_data(self, data):
        if self.skip == 0:
            t = data.strip()
            if t:
                self.parts.append(t + ' ')

def extract(fname):
    with open(fname) as f:
        data = json.load(f)
    html = data['data']['html']
    p = TextExtractor()
    p.feed(html)
    text = ''.join(p.parts)
    text = re.sub(r'\n\s*\n+', '\n', text)
    return data['data'].get('title',''), text

title, text = extract(sys.argv[1])
print("TITLE:", title)
print("=" * 60)
print(text[:8000])
