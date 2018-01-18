## Components

Introducing bootstrap components into the wiki text is the main goal of
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
* [Well](#well)

### Accordion
An accordion groups collapsible [panels](#panel) together to a single
unit in a way, that opening one panel closes all others.

Note that panels inside an accordion are collapsible by default. You
do not have to set that attribute.

See also:
* [Collapse](#collapse)
* [Panel](#panel)

#### Example usage
```
<bootstrap_accordion [..]>
  <bootstrap_panel [..]>Content text for the first panel</bootstrap_panel>
  <bootstrap_panel [..]>Content text for the second panel</bootstrap_panel>
  <bootstrap_panel [..]>Content text for the third panel</bootstrap_panel>
</bootstrap_accordion>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_collapse.asp

-------------------------------------------------------------------------
### Alert
Provide contextual feedback messages for typical user actions with the
handful of available and flexible alert messages.

See also:
* [Well](#well)

#### Example usage
```
<bootstrap_alert [..]>Message text</bootstrap_alert>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

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
it is ignored[(?)](#why-use-no-values).

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_alerts.asp
* https://getbootstrap.com/docs/3.3/components/#alerts

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
The following attributes can be used inside the parser function:

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_badges_labels.asp
* https://getbootstrap.com/docs/3.3/components/#badges

-------------------------------------------------------------------------
### Button
Bootstrap provides different styles of buttons that can link to any target.

#### Example usage
```
{{#bootstrap_button: target | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

##### Active
Having this attribute simply present or set to a non-[_no value_](#no-values)
makes a button appear pressed.

You can also set this attribute to any _no value_, in which case
it is ignored[(?)](#why-use-no-values).

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
it is ignored[(?)](#why-use-no-values).

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

##### Text
This text will be displayed on the button. If omitted, the target is
used.

If you supply an image tag, it is stripped of any link tags and then
be used inside the button. Best use a transparent image or match image
background with button color.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_buttons.asp

-------------------------------------------------------------------------
### Carousel
The Carousel component is for cycling through elements, like a carousel (slide show).


#### Example usage
```xml
{{#bootstrap_carousel: [[File:Image1|..]] | [[File:Image2|..]] | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_carousel.asp

-------------------------------------------------------------------------
### Collapse
Collapses are useful when you want to hide and show large amount of content.

See also:
* [Accordions](#accordion) consist of multiple collapsible elements
* A [panel](#panel) can also be _collapsible_.
* [Modals](#modal) can also be used to hide and show content.

#### Example usage
```
<bootstrap_collapse text="Collapse button text|[[File:TriggerImage.png|..]" [..]>Text inside the collapse</bootstrap_collapse>
```

#### Allowed Attributes
This uses all the allowed attributes of the [button](#button)
and they will be used in the same manner. Exceptions follow:

##### Text
This is a __mandatory__ field.

If you supply text, a [button](#button) will be generated and used
as the trigger for the collapse.

If you supply an image tag, it is stripped of any link tags and then
be used as the trigger element. In this case, all but the attributes
_class_, _style_, and _id_ will be ignored.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_collapse.asp

-------------------------------------------------------------------------
### Icon
Insert the glyph-icon identified by the icon name you provided. See
[online](https://getbootstrap.com/docs/3.3/components/#glyphicons) for
a list of available names.

The name is the string after the "glyphicon glyphicon-"-part. See example.

#### Example usage
```
{{#bootstrap_icon: icon-name}}
<!-- inserting an asterisk -->
{{#bootstrap_icon: asterisk}}
```

#### Allowed Attributes
None.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_glyphicons.asp
* https://getbootstrap.com/docs/3.3/components/#glyphicons

-------------------------------------------------------------------------
### Jumbotron
A jumbotron indicates a big box for calling extra attention to some special
content or information.

A jumbotron is displayed as a grey box with rounded corners. It also enlarges
the font sizes of the text inside it.

See also:
* [Modal](#modal)
* [Well](#well)

#### Example usage
```
<bootstrap_jumbotron [..]>Content of the jumbotron</bootstrap_jumbotron>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_jumbotron_header.asp
* https://getbootstrap.com/docs/3.3/components/#jumbotron

-------------------------------------------------------------------------
### Label
Labels are used to provide additional information about something.

See also:
* [Badge](#badge)

#### Example usage
```
{{#bootstrap_label: label text | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

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

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_badges_labels.asp
* https://getbootstrap.com/docs/3.3/components/#labels

-------------------------------------------------------------------------
### Modal
The Modal component is a dialog box/popup window that is displayed on top
of the current page. Note that it is not 100% compatible with the vector
skin. You might be able to notice a slight "wobble" when activating the
modal.

See also:
*  consist of multiple collapsible elements
* [Jumbotron](#jumbotron) can also be used to emphasize content
* [Accordions](#accordion), [panels](#panel), or [collapses](#collapse)
    are another way to show/hide content.

#### Example usage
```
<bootstrap_modal [..]>Content of the modal</bootstrap_modal>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

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

##### Footer
All you supply here will be inserted into the footer area of the modal.

##### Heading
All you supply here will be inserted into the header area of the modal.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Size
You can choose a size for your button. Possible options are:
* sm
* md (default)
* lg

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

##### Text
This is a __mandatory__ field.

If you supply text, a [button](#button) will be generated and used
as the trigger for the collapse.

If you supply an image tag, it is stripped of any link tags and then
be used as the trigger element.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_modal.asp

-------------------------------------------------------------------------
### Panel
A panel in bootstrap is a bordered box with some padding around its content.

See also:
* [Accordion](#accordion) uses panels to work
* [Collapse](#collapse) or [modal](#panel) (if your looking for
    more collapsible components)

#### Example usage
```xml
<bootstrap_panel [..]>Content text for the panel</bootstrap_panel>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

##### Active
When uses inside an [accordion](#accordion), having this attribute
simply present or set to a non-[_no value_](#no-values) expands this
panel.

You can also set this attribute to any _no value_, in which case
it is ignored[(?)](#why-use-no-values).

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Collapsible
Even not inside an accordion, a panel can be made collapsible. Simply
having this attribute present or set to a non-[_no value_](#no-values)
accomplishes this.

You can also set this attribute to any _no value_, in which case
it is ignored[(?)](#why-use-no-values).

##### Color
Sets the color for this component.

Allowed Values are
* default
* primary
* success
* info
* warning
* danger

##### Footer
All you supply here will be inserted into the footer area of the panel.

##### Heading
All you supply here will be inserted into the header area of the panel.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_panels.asp
* https://getbootstrap.com/docs/3.3/components/#panels

-------------------------------------------------------------------------
### Popover
The Popover component is similar to tooltips or collapses; it is a pop-up
box that appears when the user clicks on an element. The difference to
tooltip is that the popover can contain much more content.

See also:
* [Tooltip](#tooltip)
* [Collapse](#collapse)

#### Example usage
```
<bootstrap_popover text="" heading="" [..]>Content for the pop up</bootstrap_popover>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

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

##### Heading
This is a __mandatory__ field.

This will be inserted into the header area of the popover.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Placement
By default, the popover will appear on the right side of the trigger
element. With this, you can place it somewhere else:
* top
* left
* bottom
* right (default)

##### Size
You can choose a size for your trigger button. Possible options are:
* xs
* sm
* md (default)
* lg

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

##### Text
This is a __mandatory__ field.

This will be used as the text for the popover button.

If you supply an image tag, it is stripped of any link tags and then
be used inside the button. Best use a transparent image or match image
background with button color.

##### Trigger
By default, the popover is opened when you click on the trigger element,
and closes when you click on the element again. You can change his
behaviour with:
* default
* focus: the popup is closed, when you click somewhere outside the
    element.
* hover: the popover is displayed as long as the mouse pointer hovers
    over the trigger element.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_popover.asp

-------------------------------------------------------------------------
### Tooltip
Displays a tooltip when hovering over an element.

See also:
* [Popover](#popover)

#### Example usage
```
{{#bootstrap_tooltip: content of the tooltip | text="" | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Placement
By default, the tooltip will appear on top of the element. With this,
you can place it somewhere else:
* top (default)
* left
* bottom
* right

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

##### Text
This is a __mandatory__ field.

This will be used as the element, the tooltip will be displayed for.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_tooltip.asp

-------------------------------------------------------------------------
### Well
The well component adds a rounded border around content with a gray background
color and some padding.

See also:
* [Alert](#alert)
* [Jumbotron](#jumbotron)

#### Example usage
```
<bootstrap_well [..]>Message text</bootstrap_well>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

##### Class
Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.

##### Id
Sets the id of the component to this value. See to it, that it is unique.

##### Size
You can choose a size for your well. Possible options are:
* sm
* md (default)
* lg

##### Style
Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_wells.asp
* https://getbootstrap.com/docs/3.3/components/#wells

-------------------------------------------------------------------------

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

### Why use no values
The problem with the "just be present and I'll react to you" attributes
is, that you cant disable them, once you put them in. In other words,
if you want to make a panel collapsible depending on the result of
another parser function, you now can have your parser function return
a no value.

#### Example
```
<!-- this does not work: -->
<bootstrap_panel {{#if:{{{1|}}}|collapsible|}}>Content text for the panel</bootstrap_panel>

<!-- this does: -->
<bootstrap_panel collapsible="{{#if:{{{1|}}}|yes|no}}">Content text for the panel</bootstrap_panel>
```