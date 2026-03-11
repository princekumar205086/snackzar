# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtain a token by calling the <b>POST /api/v1/login</b> endpoint with your email and password. The token is returned in the response body.
