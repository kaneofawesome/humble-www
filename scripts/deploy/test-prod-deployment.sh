#!/bin/bash

# Test script for production environment deployment
# Usage: ./test-prod-deployment.sh

set -e

echo "========================================="
echo "Production Deployment Test Script"
echo "========================================="

# Configuration
PROD_URL="https://www.humblewizards.com"
EXPECTED_ENV="prod"
EXPECTED_DEBUG="0"

echo ""
echo "1. Testing if production site is accessible..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$PROD_URL")
if [ "$HTTP_STATUS" -eq 200 ] || [ "$HTTP_STATUS" -eq 301 ] || [ "$HTTP_STATUS" -eq 302 ]; then
    echo "   ✓ Site is accessible (HTTP $HTTP_STATUS)"
else
    echo "   ✗ Site returned HTTP $HTTP_STATUS"
    exit 1
fi

echo ""
echo "2. Verifying production mode (no debug)..."
RESPONSE_HEADERS=$(curl -sI "$PROD_URL")
if echo "$RESPONSE_HEADERS" | grep -q "X-Debug-Token"; then
    echo "   ✗ Debug token found - production should not have debug enabled!"
    exit 1
else
    echo "   ✓ No debug headers found (production mode confirmed)"
fi

echo ""
echo "3. Testing critical pages..."
PAGES=("/" "/contact" "/services" "/about")
for PAGE in "${PAGES[@]}"; do
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$PROD_URL$PAGE")
    if [ "$STATUS" -eq 200 ] || [ "$STATUS" -eq 301 ] || [ "$STATUS" -eq 302 ]; then
        echo "   ✓ $PAGE - OK (HTTP $STATUS)"
    else
        echo "   ✗ $PAGE - Failed (HTTP $STATUS)"
    fi
done

echo ""
echo "4. Checking theme persistence..."
# Test with a dark theme cookie
THEME_TEST=$(curl -s -H "Cookie: theme=dark" "$PROD_URL" | grep -o 'data-theme="[^"]*"' | head -1)
if echo "$THEME_TEST" | grep -q 'data-theme="dark"'; then
    echo "   ✓ Theme persistence working"
else
    echo "   ⚠ Theme persistence needs verification"
fi

echo ""
echo "5. Performance check..."
TIME_TOTAL=$(curl -s -o /dev/null -w "%{time_total}" "$PROD_URL")
if (( $(echo "$TIME_TOTAL < 1.0" | bc -l) )); then
    echo "   ✓ Page load time: ${TIME_TOTAL}s (< 1s - excellent)"
elif (( $(echo "$TIME_TOTAL < 2.0" | bc -l) )); then
    echo "   ✓ Page load time: ${TIME_TOTAL}s (< 2s - good)"
else
    echo "   ⚠ Page load time: ${TIME_TOTAL}s (> 2s - needs optimization)"
fi

echo ""
echo "6. Security headers check..."
SECURITY_HEADERS=$(curl -sI "$PROD_URL")
CHECKS_PASSED=0

if echo "$SECURITY_HEADERS" | grep -qi "X-Frame-Options"; then
    echo "   ✓ X-Frame-Options header present"
    ((CHECKS_PASSED++))
fi

if echo "$SECURITY_HEADERS" | grep -qi "X-Content-Type-Options"; then
    echo "   ✓ X-Content-Type-Options header present"
    ((CHECKS_PASSED++))
fi

if [ $CHECKS_PASSED -eq 0 ]; then
    echo "   ⚠ Consider adding security headers"
fi

echo ""
echo "7. Asset compilation check..."
if curl -s "$PROD_URL" | grep -q "asset-map.*compiled"; then
    echo "   ✓ Asset compilation appears successful"
else
    echo "   ⚠ Could not verify asset compilation"
fi

echo ""
echo "========================================="
echo "Production Deployment Test Complete!"
echo "========================================="
echo ""
echo "Manual verification recommended:"
echo "1. Check GitHub Actions for tag-triggered workflow"
echo "2. Verify APP_ENV=prod in server environment"
echo "3. Test actual functionality (forms, database operations)"
echo "4. Monitor error logs for any issues"
echo "5. Verify SSL certificate is valid"
echo ""

# Tag validation reminder
echo "Remember: Only tags starting with 'v' trigger production deployments"
echo "Example: git tag -a v1.0.0 -m 'Release version 1.0.0'"
echo ""