
My objective was/is to save sensitive data in a MySQL database in a responsible way, and be able to read/write it programmatically in a PHP web application. Asymmetric encryption would be best, but is not practical here. Symmetric encryption with a strong algorithm and hard-to-guess cipher is acceptable, but not if we store the cipher in plain text on the same server where the database credentials also live in plain text! That's a bit like locking your front door, then hanging your house keys on a hook on the exterior of the door.

This is definitely a work in progress, subject to change, but... The working idea as of now is to store the cipher in [vault](https://vaultproject.io); configure TLS authentication so that our PHP application can log in, and then create a token that allows its bearer to read the cipher. Using response-wrapping and log-auditing it should be possible to check once every minute or so that the tokens we dish out are being used as intended.

Note:  this is most certainly no substitute for reading the docs. It's intended, among other things, as a memory aide for my aging brain.

## prerequisites

Install, intialize, unseal vault, have your root token in hand. My recommendation is to get your TLS enabled now, even in development, because if you're not already proficient with all that (I'm not very), it's time to start learning to deal with TLS certificates and all the accompanying acronym soup.

## write policies

### create read-secret.hcl

```
path "secret/data" {
  policy = "read"
}
```

### create create-token.hcl

```
path "auth/token/create/read-secret" {
	policy = "write"

}
```
### store policies in vault

logged in as root (or close enough), write the policies

``vault policy-write read-secret read-secret.hcl``

``vault policy-write create-token create-token.hcl``

### create a named token role... thingy

``vault write auth/token/roles/read-secret allowed_policies=read-secret``

_Note to self: find out how to enforce better security on the token/roles thing, i.e., require response-wrapping, a short TTL and modest number of uses (either 1, or 2 most likely)._

## configure [auth/cert backend](https://www.vaultproject.io/docs/auth/cert.html)

Prerequisites include [creating the certificates](https://jamielinux.com/docs/openssl-certificate-authority/). This will keep you busy for a while if you haven't done it yet, so clear your schedule.

With your certificates ready to go, 

```
vault write auth/cert/certs/web display_name=web policies=create-token \
	certificate=@/path/to/your/cert.pem 
```
Now we have a user (in a manner of speaking) who can log in and create authentication tokens with which the bearer can read the secret. 

## authenticate via TLS

Let's see if it works.
```
vault auth -method=cert -client-cert=/path/to/your/cert.pem \
    -client-key=/path/to/your/key.pem
```

```
Successfully authenticated! You are now logged in.
The token below is already saved in the session. You do not
need to "vault auth" again with the token.
token: 07db20e8-8e14-f1eb-1352-adff380ef28e
token_duration: 36000
token_policies: [create-token default]
```
Yay! Try reading the secret:

**vault read secret/data**
```
Error reading secret/data: Error making API request.

URL: GET https://vault.sdnyinterpreters.org:8200/v1/secret/data
Code: 403. Errors:

* permission denied
```

As expected, very good. Now get a token that _can_ read the secret:

**vault token-create -role=read-secret**

```
Key            	Value
---            	-----
token          	ef2fb0d1-1644-937f-5326-3c6270abc3ba
token_accessor 	522c0a9d-7897-a670-e511-650d37ea6d20
token_duration 	768h0m0s
token_renewable	true
token_policies 	[default read-cipher]
```
Authenticate with the token we just obtained:

**vault auth ef2fb0d1-1644-937f-5326-3c6270abc3ba**
```
Key            	Value
---            	-----
token          	ef2fb0d1-1644-937f-5326-3c6270abc3ba
token_accessor 	522c0a9d-7897-a670-e511-650d37ea6d20
token_duration 	768h0m0s
token_renewable	true
token_policies 	[default read-cipher]
```
and try reading the cipher, so we can encrypt/decrypt things our database:

**vault read secret/data**

```
Key             	Value
---             	-----
refresh_interval	768h0m0s
cipher          	6012788e2629b9dc6d3f35b2335f0c1a39bcdb9fb675774ba9d4895234c535fe
```





