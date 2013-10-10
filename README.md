php-sqrl
========

PHP implementation written to test the android app. It's done fast and is far from production code. But it works, with some shortcuts. If any one want to fix them please send me a pull request. This code also works with php 5.2. I think thats important since a lot of hosting providers still use that. 

## Shortcuts
1. The signature is sent to http://ed25519.herokuapp.com/api/Verify for verification. Not done in PHP
2. QR code is done by Google API. This should be easy to fix with a lib.