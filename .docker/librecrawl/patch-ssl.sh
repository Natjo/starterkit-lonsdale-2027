#!/bin/sh
# Patch LibreCrawl to ignore SSL errors (for self-signed certs in local dev)

# 1) crawler.py: disable SSL verification on the requests.Session
sed -i '/self.session = requests.Session()/a\        self.session.verify = False\n        import urllib3; urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)' \
    /app/src/crawler.py

# 2) js_renderer.py: add ignore_https_errors=True to Playwright browser contexts
sed -i 's/context = await self.browser.new_context(/context = await self.browser.new_context(\n                    ignore_https_errors=True,/' \
    /app/src/core/js_renderer.py
