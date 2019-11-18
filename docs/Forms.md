Forms
=====

1. [Form](#form)
2. [Inputs](#inputs)
3. [Complete Example](#complete-example)

Goji includes a form handler class. You can create your form in PHP so you can render, validate and
reuse it very easily.

All the elements we will see inherit from `FormObjectAbstract`, this means they all have
some public methods in common:

- `hasClass(string $class): bool`
- `getClasses(): array`
- `addClasses(string|array $classes): FormObjectAbstract`
- `addClass(string|array $classes): FormObjectAbstract`
- `setClasses(string|array $classes): FormObjectAbstract`
- `removeClasses(string|array $classes): FormObjectAbstract`
- `removeClass(string|array $classes): FormObjectAbstract`
- `clearClasses(): FormObjectAbstract`
- `renderClassList(): string`
- `hasId(): bool`
- `getId(): string`
- `setId(string $id): FormObjectAbstract`
- `removeId(): FormObjectAbstract`
- `hasAttribute(string $key): bool`
- `getAttribute(string $key): string`
- `getAttributes(): array`
- `setAttribute(string $key, $value = null): FormObjectAbstract`
- `setAttributes(array $attributes): FormObjectAbstract`
- `removeAttribute(string $key): FormObjectAbstract`
- `removeAttributes(array $keys): FormObjectAbstract`
- `renderAttributes(bool $skipValueAttribute = false, bool $addSlashes = true, $dontRender = []): string`, render the attributes only
- `render(): void`, render the whole element

Form
----

A form is represented by a `Goji\Form\Form` class. The `Form` constructor can have three
optional parameters (that can also be set later via the setters): `action`,
`method` and `enctype`.

`Form` has several useful method, like:

- `hydrate(): void`, fills matching $_POST data into the fields (as well as $_FILES,
  but $_FILES names cannot be of array type like `contact[profile-pic]`, they must use
  regular names).
- `addInput(FormElementAbstract $input): FormElementAbstract`
- `isValid(&$detail = null): bool`, checks the validity of all fields and add the names
  of the invalid ones into `$detail`.
- `getInputBy(string $attribute, $value): ?FormElementAbstract`, returns the form input
  that has the specified attribute with the specified value.
- `getInputByName(string $name): ?FormElementAbstract`, shortcut to the previous one that
  queries the `name` attribute.
- `getInputByID(string $id): ?FormElementAbstract`, same but but the `id` attribute.

To add inputs to the form, use the `addInput(FormElementAbstract $input): FormElementAbstract`
method. This method accepts an input as parameter and returns it.

This might seem strange (it returns what it receives), but it actually make for a good shortcut.
With this you don't have to instantiate the input into a variable to "prepare" it:

```php
$form = new Form();
    $form->addClass('form__contact');
        $form->addInput(new InputText()) // Returns the InputText
             ->setAttribute('name', 'contact[name]'); // Building onto the same InputText
```

Note that if you add an `InputFile` input, the `enctype` will automatically be switched to
`multipart/form-data`.

Inputs
------

Inputs all inherit from `FormElementAbstract` and (which inherits from `FormObjectAbstract`),
this means they all share some public methods:

- `__construct(callable $isValidCallback = null, bool $forceCallbackOnly = false, callable $sanitizeCallback = null)`
- `getValue()`
- `setValue($value, $updateValueAttribute = false): FormElementAbstract`, returns itself
- `isValid(): bool`

### Validation

Most of the inputs have some basic validation schemes built into them (go into the source code
and look at their `isValid(): bool` implementation). For example, a basic `InputText` will be valid
if it is *not empty if required* and *shorter than maxlength if maxlength is set* and *longer than
minlength if minlength is set*.

If you feel the basic validity inspection isn't enough, you can specify a callback function (with
a parameter accepting the value, and returning a boolean), that will be added on top of the basic
verification.

If you want to completely bypass the basic validation in favor of your callback, set `$forceCallbackOnly`
to `true` in the constructor.

```php
$isValidCallback = function($value): bool {

    if ($value is valid)
        return true;
    else
        return false;
};

$textInput = new InputText($isValidCallback); // callback + basic verification
$textInput = new InputText($isValidCallback, true); // callback only
```

### Sanitization

Sanitization allows to to transform the received value, like normalizing emails.

Sanitization works similarly to validation, just pass the callback as third argument:

```php
$sanitizeCallback = function($value) {

    if (empty($value))
        return $value;

    $value = mb_strtolower($value);
    return filter_var($value, FILTER_SANITIZE_EMAIL);
};

$textInput = new InputText(null, false, $sanitizeCallback);
```

### All available inputs

***Note:** Classes that inherit from the one above are indented one level.*

***Note:** Classes that render in paired tags (with an `<opening>` and `</closing>` tag),
instead of a single tag (`<auto-closing>`) tag use the `textContent` attribute to set
the inner content.*

- `InputButton`
    - `InputButtonElement`, uses `textContent`
    - `InputButtonImage`
    - `InputButtonReset`
    - `InputButtonSubmit`
- `InputCheckbox`, uses `textContent`
    - `InputRadioButton`, uses `textContent`
- `InputCustom`, uses `textContent`
- `InputFile`
- `InputHidden`
- `InputLabel`, uses `textContent`
- `InputSelect`
- `InputSelectOption`, uses `textContent`
- `InputSelectOptionGroup`
- `InputText`
    - `InputTextArea`
    - `InputTextEmail`
    - `InputTextPassword`
    - `InputTextSearch`
    - `InputTextTel`
    - `InputTextUrl`

#### InputCustom

The `InputCustom` is an empty input, where you must specify the input opening tag and
closing tag as first and second parameters. Note that `%{ATTRIBUTES}` will be replaced
by the generated attributes.

```php
$input = new InputCustom('<custom %{ATTRIBUTES}>', '</custom>');
    $input->setAttribute('name', 'custom-element')
          ->setAttribute('textContent', 'My Custom Element');
```

Will render as:

```html
<custom name="custom-element">My Custom Element</custom>
```

It is your job to set the appropriate validity check callback.

#### InputLabel

`InputLabel` has a special method called `setSideInfo(string $tag, array $attributes = null, string $textContent = ''): InputLabel`.

With this method you can add a side info to that label. A side info will appear right to the
label and in a smaller font if you use Goji's default styling.

The first parameter is the HTML tag name, the second an associative array of attribute name/value
and the last the inner content of the tag.

```php
$form->addInput(new InputLabel())
    ->setAttribute('textContent', 'Password')
    ->setSideInfo('a', array('href' => '#'), 'Forgot password?');
```

Will render as (Goji adds some formatting on top of it to conform to default styling rules):

```php
<div class="form__label-relative">
    <label>Password</label>
    <a href="#" class="form__side-info">Forgot password?</a>
</div>
```

Complete Example
----------------

```php
$form = new Form();
    $form->addClass('form__contact');

        $form->addInput(new InputLabel())
             ->setAttribute('for', 'contact__name')
             ->addClass('required')
             ->setAttribute('textContent', 'Enter your name:')
             ->setSideInfo('a', array('class' => 'label'), 'More infos?');

        $form->addInput(new InputText())
             ->setAttribute('name', 'contact[name]')
             ->setId('contact__name')
             ->setAttribute('placeholder', 'John Doe')
             ->setAttribute('required');

        $form->addInput(new InputTextArea())
             ->setAttribute('name', 'contact[message]')
             ->setId('contact__message')
             ->addClass('big')
             ->setAttribute('placeholder', 'Message')
             ->setAttribute('required');

        $inputSelect = new InputSelect();
            $inputSelect->setAttribute('name', 'contact[preference]');

            $optGroup = new InputSelectOptionGroup();
            $optGroup->setAttribute('label', 'Group');
            $optGroup->addOption(new InputSelectOption())
                     ->setAttribute('value', 'hello')
                     ->setAttribute('textContent', 'Hello');
            $optGroup->addOption(new InputSelectOption())
                     ->setAttribute('value', 'world')
                     ->setAttribute('textContent', 'World');

            $inputSelect->addOption($optGroup);

            $inputSelect->addOption(new InputSelectOption())
                        ->setAttribute('value', 'foo')
                        ->setAttribute('textContent', 'Foo');

            $inputSelect->addOption(new InputSelectOption())
                        ->setAttribute('value', 'bar')
                        ->setAttribute('textContent', 'Bar');

        $form->addInput($inputSelect);

        $form->addInput(new InputCheckBox())
              ->setAttribute('name', 'contact[remember-me]')
              ->setId('contact__remember-me')
              ->addClass('toggle')
              ->setAttribute('textContent', 'Remember my details');
              
        $form->addInput(new InputRadioButton())
             ->setAttribute('name', 'contact[receive-confirmation]')
             ->setId('contact__receive-confirmation')
             ->setAttribute('checked')
             ->setAttribute('value', 'yes')
             ->setAttribute('textContent', 'Yes');
        $form->addInput(new InputRadioButton())
             ->setAttribute('name', 'contact[receive-confirmation]')
             ->setId('contact__receive-confirmation')
             ->setAttribute('value', 'no')
             ->setAttribute('textContent', 'Nope');

        $form->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));

        $form->addInput(new InputButtonSubmit());

    if ($this->m_app->getRequestHandler()->getRequestMethod() === HttpMethodInterface::HTTP_POST)
        $form->hydrate(); // Call hydrate() to auto-fill $_POST values into the form elements

...

// In the view
$form->render(); // Prints the raw form

...

// In the controller
if ($this->m_app->getRequestHandler()->getRequestMethod() === HttpMethodInterface::HTTP_POST) {

    if ($form->isValid())
        echo "Thanks!";
    else
        echo "Your form contains errors.";
}
```
