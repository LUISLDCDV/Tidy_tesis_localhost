FROM node:18-alpine

WORKDIR /app

COPY package*.json ./

RUN npm install -g npm@latest && \
    npm cache clean --force && \
    npm install

COPY . .

EXPOSE 8080

CMD ["npm", "run", "serve"] 