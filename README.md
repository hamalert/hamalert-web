# HamAlert web application

This is the source code for the HamAlert web application, hosted at https://hamalert.org. It allows users to register, manage their triggers and alert destinations, and to simulate spots.

It dates back to 2017 and uses somewhat ancient technologies. The backend is written in simple PHP, and the frontend uses a mixture of classic form POST based logic and Ajax with a client-side templating engine called [JsRender](https://www.jsviews.com) for the trigger editor.

A MongoDB is used for storing user and trigger information (which is also used by the [spot processing backend](http://github.com/hamalert/hamalert-server)).
