## Components

Introducing bootstrap components into the wiki test is the main goal of
this extension. Depending on your configuration, none, some, or all of
the following components are available to be used inside the wiki text:

* [Accordion](#accordion)
* [Alert](#alert)
* [Badge](#badge)
* [Button](#button)
* [Carousel](#carousel)
* [Collapse](#icon)
* [Icon](#jumbotron)
* [Jumbotron](#jumbotron)
* [Label](#label)
* [Modal](#modal)
* [Panel](#panel)
* [Popover](#popover)
* [Tooltip](#tooltip)

### Accordion


https://www.w3schools.com/bootstrap/bootstrap_collapse.asp

### Alert
Provide contextual feedback messages for typical user actions with the
handful of available and flexible alert messages.

#### Example usage
```
<bootstrap_alert [..]>Message text</bootstrap_alert>
```

#### Allowed Attributes
The following attributes can be used inside the tag.

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Color
Sets the color for this component.

Allowed Values are
* default
* primary
* success
* info
* warning
* danger

##### Dismissible
If present or set to any value, the alert will get a dismiss-button.
If you set dismissible to _fade_, the alert will fade out when dismissed.

You can also set this attribute to any [_no_ value](#no-values), in which case
it is ignored.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://getbootstrap.com/docs/3.3/components/#alerts
* https://www.w3schools.com/bootstrap/bootstrap_alerts.asp

-------------------------------------------------------------------------
### Badge
Easily highlight new or unread items by adding a badge component to them.
They can be best utilized with a numerical _text_, but any string will do
fine.

See also:
* [Label](#label)

#### Example usage
```
{{#bootstrap_badge: text | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the tag.

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://getbootstrap.com/docs/3.3/components/#badges
* https://www.w3schools.com/bootstrap/bootstrap_badges_labels.asp

-------------------------------------------------------------------------
### Button
Bootstrap provides different styles of buttons that can link to any target.

#### Example usage
```
{{#bootstrap_button: target | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the tag.

##### Active
Having this attribute simply present or set to a non-[_no value_](#no-values)
makes a button appear pressed.

You can also set this attribute to any _no value_, in which case
it is ignored.

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Color
Sets the color for this component.

Allowed Values are
* default
* primary
* success
* info
* warning
* danger

##### Disabled
Having this attribute simply present or set to a non-[_no value_](#no-values)
disables the button.

You can also set this attribute to any _no value_, in which case
it is ignored.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Size
You can choose a size for your button. Possible options are:
* xs
* sm
* md (default)
* lg

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_buttons.asp


ToDo
  * introduction
  * accordion
  * carousel
  * collapse
  * icon
  * jumbotron
  * label
  * modal
  * panel
  * popover
  * tooltip

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

## Addendum

### No Values
A no value is any of the following (case sensitive):
- no
- 0
- false
- off
- disabled
- ignored
- whatever means "no" in your content language