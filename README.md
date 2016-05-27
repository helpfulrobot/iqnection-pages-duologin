Secures the admin are with Duo Security

Add to your sites config.yml file:
---
DuoLogin:
  IKEY: 'Integration Key from Duo'
  SKEY: 'Secret Key from Duo',
  HOST: 'Host from Duo'
  AKEY: 'Key/hash you generate'
---