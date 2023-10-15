# wp-long-unupdated-notifier

## Overview
When a certain period of time has elapsed since the date and time of posting, a message is output before the body of the post.

## Features
- Output messages can be customized
- Can specify the number of years elapsed

## Unimplemented Features
### Plugin's own color definition
Although the selection function is implemented in the admin panel, the plugin's own color styles are not defined by CSS, but simply output the following CSS classes on a Bootstrap basis. Therefore, if the CSS in which these classes are defined is not loaded, no color styles are applied.