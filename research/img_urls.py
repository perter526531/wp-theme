import json, re, sys
from urllib.parse import urljoin

for fname in sys.argv[1:]:
    with open(fname) as f:
        data = json.load(f)
    html = data['data']['html']
    urls = set()
    # img src / data-src / srcset
    for m in re.finditer(r'<img[^>]+>', html):
        tag = m.group(0)
        for attr in re.finditer(r'(?:src|data-src)="([^"]+)"', tag):
            u = attr.group(1)
            if 'uploads' in u or u.startswith('http'):
                urls.add(u)
        for m2 in re.finditer(r'srcset="([^"]+)"', tag):
            for part in m2.group(1).split(','):
                u = part.strip().split(' ')[0]
                if u:
                    urls.add(u)
    print(f"=== {fname} ===")
    for u in sorted(urls):
        if 'dazhenglobal.com' in u and ('uploads' in u):
            print(u)
