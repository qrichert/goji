Translation
===========

1. [Loading the Translator](#loading-the-translator)
2. [Using the Translator](#using-the-translator)
3. [Creating Translation Files](#creating-translation-files)
4. [Pluralization](#pluralization)

Loading the Translator
----------------------

Let's take a look at the home page example :

```php
$tr = new Translator($this->m_app); // You must pass a reference to App
    $tr->loadTranslationResource('%{LOCALE}.tr.xml');
```

Let's assume the current locale is `en_AU`.

So the previous code will first look for `en_AU.tr.xml`. So if you have a `/translation/en_AU.tr.xml`
file, it will load this resource (to load more than one resource, just call the method for the other
files).

If it can't find it, it will then look for `en.tr.xml`. This is because maybe you support different
locales like en_AU, en_GB, en_NZ or en_US. This only means they have different URLs but they can all
share the same text. It’s up to you to have a specific version for each or use the same for all.

In any case, if Goji finds a specific version (country code + region, like `en_US`) it will use this
one, if it doesn’t it will fall back to the general version (country code alone, like `en`).

Using the Translator
--------------------

There are three equivalent ways to get text from the `Translator`.

```php
$tr->_('HELLO_WORLD'); // Preferred
$tr->tr('HELLO_WORLD');
$tr->translate('HELLO_WORLD');
```

`_()` and `tr()` are aliases of `translate()`. `_()` is preferred because it doesn't clutter the code.

Creating Translation Files
--------------------------

There are two types of translation files, PHP constants and XML.

XML is recommended. PHP are more for very light projects with just a few strings to translate and with
no need of pluralization. The `translate()` doesn't work with PHP constants, you must use them as you
always do :

```php
define('PHP_CONSTANT', 'value');
...
echo PHP_CONSTANT;
```

The XML files consist of segments with an ID and ordered by page. `<page>` without an ID are part of
the global scope and accessible everywhere.

```xml
<page id="home">
    <segment id="FOO_BAR">foobar</segment>
</page>
```

To use the previous segment, just do:

```
echo $tr->_('FOO_BAR'); // echoes 'foobar'
```

Pluralization
-------------

Goji's translator supports pluralization. Pluralization just means that you can have several version
for a segment depending on the count of something, like 1 berr**y**, but 2 berr**ies**.

To do this in XML, use the `<alternative>` tag.

The `count` attribute is a regex pattern that will be matched again the number (the number is converted
to a string beforehand). The pattern will be used as is, so `^0$` matches `'0'` but `0` can match `'101'`
as well. If `count` equals `rest`, it means "for all the rest", like an `else` in a condition. 

`%{COUNT}` will be replaced by the actual count.

```xml
<page id="home">
    <segment id="FOO_BAR">
        <alternative count="^0$">Sorry, no foobars :(</alternative>
        <alternative count="^1$">There's only one foobar, %{COUNT}...</alternative>
        <alternative count="rest">There are %{COUNT} foobars.</alternative>
	</segment>
</page>
```

To use the alternatives, provide the count as a second parameter for the `translate()` method:

```php
echo $tr->_('FOO_BAR', 0); // Sorry, no foobars :(
echo $tr->_('FOO_BAR', 1); // There's only one foobar, 1...
echo $tr->_('FOO_BAR', 2); // There are 2 foobars.
echo $tr->_('FOO_BAR', 42); // There are 42 foobars.
```
