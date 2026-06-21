FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy package.json and install dependencies
COPY package*.json ./
RUN npm install

# Expose Vite default port
EXPOSE 5173

# Command to start Vite dev server, allowing external access (--host)
CMD ["npm", "run", "dev", "--", "--host"]
