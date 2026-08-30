FROM python:3.11-slim

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    wget \
    gnupg \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libpango-1.0-0 \
    libcairo2 \
    libasound2 \
    libxshmfence1 \
    fonts-liberation \
    && rm -rf /var/lib/apt/lists/*

RUN git clone --depth 1 https://github.com/PhialsBasement/LibreCrawl.git .

RUN pip install --no-cache-dir -r requirements.txt \
    && playwright install chromium

COPY librecrawl/patch-ssl.sh /tmp/patch-ssl.sh
RUN chmod +x /tmp/patch-ssl.sh && /tmp/patch-ssl.sh && rm /tmp/patch-ssl.sh

EXPOSE 5000

ENV FLASK_APP=main.py
ENV PYTHONUNBUFFERED=1

CMD ["python", "main.py"]
