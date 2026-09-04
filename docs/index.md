# Magic Compose

Simple solution for magic methods and method composition using PHP attributes.

PHP traits can't compose magic methods — when multiple traits define `__get`, only one wins. Magic Compose dispatches to multiple handlers by priority, with chainable `MagicExtend` for parent method interception.

## Table of contents

- [Installation](installation.md)
- [API](api.md)
- [Integration](integration.md)
