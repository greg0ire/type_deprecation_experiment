# How to deprecate a type in PHP

So you have this class or interface you want to rename to something else,
because you need to move that type to another package, or you have new coding
standard rules that need to be applied to its name. One solution is to use
inheritance and move all the code to the new type:

```php
<?php

// LegacyFoo.php

/**
 * @deprecated please use ShinyNewFoo instead!
 */
interface LegacyFoo extends ShinyNewFoo
{
}
```

This approach is great until your users write code that expects the old type,
and get the new type from your code instead:

```php
<?php

class Bar // registered and invoked by your library
{
    public function __invoke(LegacyFoo $foo) // crash
    {
    }
}
```

But fear not, there is another approach, that consists in creating an alias for
your type.

```php
<?php

// LegacyFoo.php

class_alias(ShinyNewFoo::class, LegacyFoo::class);
```

This creates an alias from interface `LegacyFoo`, to interface `ShinyNewFoo`.
You read that right, although it is `class_alias` and I used the `class`
constant, both work for interfaces. But this is not enough, because nothing
guarantees `LegacyFoo` will be autoloaded at some point. Using that type in a
parameter type hint does not trigger autoload, because surely the type should
be autoloaded when the object passed as parameter is instantiated. Well, this
optimization does not work for type aliases, which means we have to manually
trigger the autoload at the bottom of `ShinyNewFoo.php`.

```php
<?php

// ShinyNewFoo.php

class_exists(ShinyNewFoo::class);
```

Think this is over? Not so fast, there is more. Now that it all works, let us
put this in production, and see it burst into flames! `class_alias` calls are
so rarely used that Composer does not look for them when generating its
autoload classmap. [Autoload
classmaps](https://getcomposer.org/doc/articles/autoloader-optimization.md) are
a way to know which files to load without checking the filesystem first, which
is faster. To trick Composer into detecting the legacy type, we can declare it
as a piece of dead code:

```php
<?php

// LegacyFoo.php

class_alias(ShinyNewFoo::class, LegacyFoo::class);

if (false) {
    class LegacyFoo extends ShinyNewFoo
    {
    }
}
```

This will also fool most IDEs into providing auto-complete.

And finally, what if we want to trigger a deprecation error when the legacy
type is used? We cannot just do it since it will be autoloaded every time we
use the new type. All we can do is implement a best-effort solution:

```php
<?php

if (!class_exists(ShinyNewFoo::class, false)) {
    @trigger_error(
        'LegacyFoo is deprecated!',
        E_USER_DEPRECATED
    );
}
```

This checks first if `ShinyNewFoo` exists, *without triggerring autoload*. If it
does not, then `LegacyFoo` is referenced somewhere and we can safely trigger a
deprecation.

Done. Wow that was hard, and I cannot say it feels very satisfying. I wish
there were a native way in php to do all this.
