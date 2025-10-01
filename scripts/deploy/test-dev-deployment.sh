#!/bin/bash

# Test script for development environment deployment
# Usage: ./test-dev-deployment.sh

set -e

echo "========================================="
echo "Development Deployment Test Script"
echo "========================================="

# Configuration
DEV_URL="https://staging.humblewizards.com"
EXPECTED_ENV="dev"
EXPECTED_DEBUG="1"

echo ""
echo "1. Testing if development site is accessible..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$DEV_URL")
if [ "$HTTP_STATUS" -eq 200 ] || [ "$HTTP_STATUS" -eq 301 ] || [ "$HTTP_STATUS" -eq 302 ]; then
    echo "   ✓ Site is accessible (HTTP $HTTP_STATUS)"
else
    echo "   ✗ Site returned HTTP $HTTP_STATUS"
    exit 1
fi

echo ""
echo "2. Checking for debug mode indicators..."
RESPONSE_HEADERS=$(curl -sI "$DEV_URL")
if echo "$RESPONSE_HEADERS" | grep -q "X-Debug-Token"; then
    echo "   ✓ Debug token header found (debug mode active)"
elif curl -s "$DEV_URL" | grep -q "app_env.*dev"; then
    echo "   ✓ Development environment detected in page"
else
    echo "   ⚠ Could not verify debug mode - may need manual check"
fi

echo ""
echo "3. Testing specific pages..."
PAGES=("/" "/contact" "/services" "/about")
for PAGE in "${PAGES[@]}"; do
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$DEV_URL$PAGE")
    if [ "$STATUS" -eq 200 ] || [ "$STATUS" -eq 301 ] || [ "$STATUS" -eq 302 ]; then
        echo "   ✓ $PAGE - OK (HTTP $STATUS)"
    else
        echo "   ✗ $PAGE - Failed (HTTP $STATUS)"
    fi
done

echo ""
echo "4. Checking theme persistence..."
# Test with a dark theme cookie
THEME_TEST=$(curl -s -H "Cookie: theme=dark" "$DEV_URL" | grep -o 'data-theme="[^"]*"' | head -1)
if echo "$THEME_TEST" | grep -q 'data-theme="dark"'; then
    echo "   ✓ Theme persistence working"
else
    echo "   ⚠ Theme persistence needs verification"
fi

echo ""
echo "5. Performance check..."
TIME_TOTAL=$(curl -s -o /dev/null -w "%{time_total}" "$DEV_URL")
if (( $(echo "$TIME_TOTAL < 2.0" | bc -l) )); then
    echo "   ✓ Page load time: ${TIME_TOTAL}s (< 2s)"
else
    echo "   ⚠ Page load time: ${TIME_TOTAL}s (> 2s - may need optimization)"
fi

echo ""
echo "========================================="
echo "Development Deployment Test Complete!"
echo "========================================="
echo ""
echo "Manual verification recommended:"
echo "1. Check GitHub Actions for successful workflow run"
echo "2. Verify APP_ENV=dev in server environment"
echo "3. Test contact form with local mail server"
echo "4. Verify database connection to development DB"
echo ""