#!/bin/bash
# cPanel Deployment Script
# Deploy healthcare application to cPanel hosting
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== cPanel Deployment Script ===${NC}"

# Define cleanup function for exit
cleanup() {
    echo -e "${YELLOW}Cleaning up...${NC}"
    exit $1
}

# Handle script interruption
trap 'cleanup 1' INT TERM

# Check Node.js and pnpm
echo -e "${YELLOW}Checking Node.js and package manager...${NC}"
node --version
pnpm --version

# Install dependencies
echo -e "${YELLOW}Installing dependencies...${NC}"
pnpm install --prefer-offline

# Build frontend
echo -e "${YELLOW}Building frontend...${NC}"
cd src/frontend
pnpm install --prefer-offline
pnpm build
echo -e "${GREEN}Frontend build complete.${NC}"

# Copy environment file
echo -e "${YELLOW}Setting up environment...${NC}"
if [ ! -f dist/env.json ]; then
    cp env.json dist/ || echo -e "${YELLOW}env.json not found, skipping...${NC}"
fi

cd ../..

# Setup public_html or appropriate directory
echo -e "${YELLOW}Setting up cPanel deployment...${NC}"
PUBLIC_HTML="${CPANEL_PUBLIC_HTML:-public_html}"

if [ -d "$PUBLIC_HTML" ]; then
    echo -e "${YELLOW}Backing up existing files...${NC}"
    cp -r "$PUBLIC_HTML" "${PUBLIC_HTML}.backup.$(date +%s)" || true
fi

echo -e "${YELLOW}Deploying to $PUBLIC_HTML...${NC}"
rm -rf "$PUBLIC_HTML"/* || true
cp -r src/frontend/dist/* "$PUBLIC_HTML/"

echo -e "${GREEN}=== Deployment Complete ===${NC}"
echo -e "${GREEN}Your application is ready at: $PUBLIC_HTML${NC}"

cleanup 0
