<?php

namespace Greg0ire\TypeDeprecationExperiment;

if (!class_exists(Foo::class, false)) {
    @trigger_error(
        'LegacyFoo is deprecated!',
        E_USER_DEPRECATED
    );
}
class_alias(
    __NAMESPACE__.'\Foo',
    __NAMESPACE__.'\LegacyFoo'
);

if (false) {
    class LegacyFoo extends Foo
    {
    }
}
