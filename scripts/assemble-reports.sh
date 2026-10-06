#!/usr/bin/env bash
# Lays the test and coverage reports out under site/reports/ so the docs
# deploy publishes them next to the API reference, at
# apis.ryankes.eu/forge-dashboard-sdk-php/reports/. Expects junit.xml,
# cobertura.xml, coverage.xml (Clover) and coverage-html/ in the working
# directory; fails if any is missing so a half-built set never ships.
set -euo pipefail

for input in junit.xml cobertura.xml coverage.xml coverage-html/index.html; do
  [ -f "$input" ] || { echo "missing $input" >&2; exit 1; }
done

out=site/reports
rm -rf "$out"
mkdir -p "$out/tests" "$out/coverage"

cp junit.xml "$out/tests/junit.xml"
cp -r coverage-html/. "$out/coverage/"
cp cobertura.xml "$out/coverage/coverage.xml"
cp coverage.xml "$out/coverage/clover.xml"

cat > "$out/tests/index.html" <<'HTML'
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Test results</title></head>
<body><h1>Test results</h1>
<ul><li><a href="junit.xml">junit.xml</a> (JUnit XML, Pest)</li></ul>
<p><a href="../">Reports</a></p></body></html>
HTML

cat > "$out/index.html" <<'HTML'
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports</title></head>
<body><h1>forge-dashboard-sdk-php reports</h1>
<ul>
<li><a href="tests/">Test results</a></li>
<li><a href="coverage/">Coverage</a> (<a href="coverage/coverage.xml">Cobertura XML</a>, <a href="coverage/clover.xml">Clover XML</a>)</li>
</ul></body></html>
HTML
