# Release History

## 0.4.2
- update `fkooman/rest`
- fix missing exception use

## 0.4.1
- fix `IntrospectionBearerValidator`

## 0.4.0
- rename `TokenIntrospection` to `TokenInfo`
- allow for alternative verification backend, include introspection backend 
  with using username/password and bearer token, see example on how to use it
- remove entitlement support
- rewrite `TokenInfo` to be more complete according to 
  `draft-ietf-oauth-introspection`, remove most of the token checking, only 
  check the type of the fields and require `active` to be present.

## 0.3.1
- update to latest `fkooman/rest` to support optional authentication

## 0.3.0
- update to latest `fkooman/rest` 

## 0.2.2
- add `toString()`, `__toString()` and `toArray()` methods

## 0.2.1
- set the token in Guzzle 3.x way

## 0.2.0
- switch back to Guzzle 3.x to support `php >= 5.3.3`
- include `Scope`, `Entitlement` and `TokenIntrospection` in the plugin now

## 0.1.1
- update dependencies

## 0.1.0 
- initial release
