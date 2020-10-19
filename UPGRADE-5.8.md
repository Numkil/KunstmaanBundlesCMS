UPGRADE FROM 5.7 to 5.8
=======================

AdminBundle
------------

Implemented a custom replacement for FosUserBundle. It is disabled by default and will be enabled by default in BundlesCMS 6.0.
If you want to enable it now you can use the following config to do so. If you depend on the showAction of the ProfileController in FosUserBundle than you have to implement your own
action after enabling the new custom routes as we do not officially support this view in the cms.
```
kunstmaan_admin:
  enable_new_cms_authentication: true
  #Only necessary when you have overriden the default Kunstmaan CMS User object as when you enable the enable_new_cms_authentication option we do not longer rely on the fos_user userclass parameter.
  admin_user_class: App\Entity\User
  ```
If you enable our custom login implementation you will also have to update your security.yaml to use our new routes. Upon changing these 2 things you are now not using anything from the FosUserBundle anymore.

```
security:
  encoders:
    Kunstmaan\AdminBundle\Entity\UserInterface: sha512

  providers:
    cms_users:
      entity: { class: App\Entity\User, property: username }

  firewalls:
    main:
      pattern: ^/([^/]*)/admin
      form_login:
        provider: cms_users
        login_path: cms_login
        check_path: cms_login
      logout:
        path: cms_logout
        target: KunstmaanAdminBundle_homepage
      anonymous:    true
      remember_me:
        secret:      "%secret%"
        lifetime: 604800
        path:     /
        domain:   ~
    dev:
      pattern:  ^/(_(profiler|wdt)|css|images|js)/
      security: false

```