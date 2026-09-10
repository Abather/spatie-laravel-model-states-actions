# spatie-laravel-model-states-actions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/abather/spatie-laravel-model-states-actions.svg?style=flat-square)](https://packagist.org/packages/abather/spatie-laravel-model-states-actions)
[![Total Downloads](https://img.shields.io/packagist/dt/abather/spatie-laravel-model-states-actions.svg?style=flat-square)](https://packagist.org/packages/abather/spatie-laravel-model-states-actions)

with this package you can display or add actions to your views using state, this package depends on `spatie/laravel-model-states` package.
## Installation

You can install the package via composer:

```bash
composer require abather/spatie-laravel-model-states-actions
```

## Usage

Follow the Doc in [ Laravel-model-states ](https://spatie.be/docs/laravel-model-states/v2/01-introduction),
but for each State you have to extends `Abather\SpatieLaravelModelStatesActions\State` :

```php
use Abather\SpatieLaravelModelStatesActions\State;
use Spatie\ModelStates\StateConfig;

abstract class PaymentState extends State
{   
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Paid::class)
            ->allowTransition(Pending::class, Failed::class)
        ;
    }
}
```

### state field

by default the package expects the state to be stored in a field called `state`, if you use another field you can pass it to the services:

```php
StateActionsService::make(Order::class, 'importance')->actions()
StateFilterService::make(Order::class, 'importance')->tableFilter()
```

the field will be passed to the actions, so the authorization and the transition will use it.

for the methods that you call on the state itself you can pass the field as well:

```php
OrderState::textColumn('importance')
OrderState::textEntry('importance')
OrderState::formSelect(Order::class, 'importance')
```

or you can override `$state_key` in your base state class and skip passing the field every time:

```php
abstract class OrderState extends State
{
    public static string $state_key = 'importance';
}
```

### state name

you can name your states as you do in Spatie package:

```php
class Paid extends PaymentState
{
    public static $name = 'paid';
}
```

the package will resolve the name back to the state class, and the value that stored in the database will be used for the select options and the filters.

### configure authorization

you can define ability name for each state that well be used to determine if the user can change the state or not as:

```php
<?php

namespace App\States\Order;

class Canceled extends OrderState
{
    protected static ?string $ability = 'cancel';
}
```

that mean it well look to ability name called `cancel` in OrderPolicy class.

also you can skip authorization by override the attribute `$skip_authorization = true`

keep in mind that the package also chick if you can transfer to the state using `canTransitionTo(Cancel::class)` that come with Spatie Package you can view it [here](https://spatie.be/docs/laravel-model-states/v2/working-with-transitions/01-configuring-transitions#content-using-transitions)

### Customize Action Icon & Color

you can custom the state color and icon by overriding the attributes `$color` & `$icon`

```php
<?php

namespace App\States\Contract;

class Canceled extends ContractState
{
    protected static ?string $color = 'danger';
    protected static ?string $icon = 'heroicon-o-cursor-arrow-rays';
}
```
for these attributes you have to follow Filament Doc about each one of them [Icon Doc](https://filamentphp.com/docs/3.x/support/icons), [Color Doc](https://filamentphp.com/docs/3.x/support/colors)
### Localization

you need to create `states.php` file to translate the state and the structure of each state should be as:

```php
    'ClassName' => [
        'title' => '', //This is the name that well be displayed.
        'label' => '', //This is the action name that used in action button.
    ],
```

### Display Current State
To display the current state in Table you can use `StateClass::stateTextColumn()` as column in table:

```php
 public static function table(Table $table): Table
{
    return $table
        ->columns([
            //Other Columns
            ContractState::textColumn(),
            //Other Columns
        ]);
}
```

if you went to display the current state in Edit or Info List you can do so by adding `state->display()` method to header actions:

```php
class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Other Actions
            $this->record->state->display(),
            //Other Actions
        ];
    }
}
```
feel free to add it to any place that accept `Filament\Actions\Action` object.

to display the state as normal `TextEntry` inside `InfoList` you can do so by using `StateClass::textEntry()`:

```php

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                //...
                ContractState::textEntry(),
                //...
            ]);
    }

```

to include the select form filed use `formSelect(static::getModel())` method:

```php
public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //....
                ContractState::formSelect(static::getModel()),
                //...
             ]);
    }
```
### View actions in Table:

you can view the available actions 'depends on authorizations as states configuration':
```php
    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->actions([
                    ...StateActionsService::make(static::getModel())->tableActions(),
            ]);
    }
```
`tableActions()` method well return an array of `Filament\Tables\Actions\Action` objects.

### View actions in any page

you can view the available actions 'depends on authorizations as states configuration' in any resource page:
```php
class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...StateActionsService::make(Contract::class)
                ->actions(),
        ];
    }
}
```

`actions()` will return an array of `Filament\Actions\Action` objects, that mean you can use it any place that accept `Action` object.

### View actions as group

you can view available actions in any resource page as group :

```php
class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            StateActionsService::make((static::$resource)::getModel(), 'importance')
                    ->excludeStates($this->record->importance)
                    ->actionsAsGroup($this->record)
        ];
    }
}
```

if you don't pass the `record` it will not display the current state.

### Config ordering actions
if you went to display available actions in specific order you can do so by overriding `$order` attribute in each State.

```php
class Approved extends PaymentState
{
    public static int $order = 1;    
}
```

```php
class Rejected extends PaymentState
{
    public static int $order = 2;
}
```

### action without confirmation modal

if you don't went the conformation modal you can set attribute `$requires_confirmation` to `false` in the state:

```php
<?php

namespace App\States\Contract;

class Canceled extends ContractState
{
    protected static ?bool $requires_confirmation = false;
}
```

or change it in the base state class:

```php
<?php

namespace App\States\Order;

use App\States\State;
use Spatie\ModelStates\Attributes;

abstract class OrderState extends State
{
    protected static ?bool $requires_confirmation = false;
}

```

this will stop confirmation modal to all states under this class.

### Notifications & live refresh

whenever a state transition completes, the package will automatically:

- send a success notification to the user.
- refresh the Livewire component that triggered the action, so the new state is reflected right away without a manual page reload.

the default notification title is:

```
States transitioned to (:state) successfully.
```

where `:state` is replaced with the state's `title()`.

you can disable the notification entirely by overriding `$send_notification` in your state:

```php
<?php

namespace App\States\Contract;

class Canceled extends ContractState
{
    protected static ?bool $send_notification = false;
}
```

or customize the title and add a body per state by overriding `$success_notification_title` and `$success_notification_body`:

```php
<?php

namespace App\States\Payment;

class Approved extends PaymentState
{
    protected static ?string $success_notification_title = 'Payment approved!';
    protected static ?string $success_notification_body = 'The payment has been marked as approved.';
}
```

since a static property can only hold a fixed value, if you need translated text (or anything that needs parameters, like `:state`) override the `successNotificationTitle()`/`successNotificationBody()` methods instead and use `__()`:

```php
<?php

namespace App\States\Payment;

class Approved extends PaymentState
{
    public static function successNotificationTitle(): ?string
    {
        return __('payments.notifications.approved', ['state' => static::title()]);
    }
}
```

if you don't override `$success_notification_title`, the default text is resolved from the package's own translation file, so it's available out of the box in both English and Arabic without any extra setup on your end.

## Add State Filters To Table

you can include state filters to your table by using `StateFilterService::make(Contract::class)->tableFilter()`as:

```php
public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                StateFilterService::make(static::getModel())
                    ->tableFilter(),
            ]);
    }
```

the filter will use the field that passed to the service, so if your state stored in another field you only need to pass it once:

```php
StateFilterService::make(static::getModel(), 'importance')
    ->tableFilter(),
```

you can hide a state from the filters by overriding `$exclude_from_filters` in the state:

```php
class Canceled extends OrderState
{
    public static bool $exclude_from_filters = true;
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mohammed Aljuraysh](https://github.com/Abather)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
