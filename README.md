# HamAlert web application

This is the source code for the HamAlert web application, hosted at https://hamalert.org. It allows users to register, manage their triggers and alert destinations, and to simulate spots.

It dates back to 2017 and uses somewhat ancient technologies. The backend is written in simple PHP, and the frontend uses a mixture of classic form POST based logic and Ajax with a client-side templating engine called [JsRender](https://www.jsviews.com) for the trigger editor.

A MongoDB is used for storing user and trigger information (which is also used by the [spot processing backend](http://github.com/hamalert/hamalert-server)).

## Running locally

The web app can be run on its own in Docker (or Podman with the `podman-docker` shim). It
needs nothing on the host but Docker: the `Dockerfile.dev` image brings PHP, Apache, the
MongoDB extension and Composer, and the checkout is bind-mounted into the container, so edits
are live without a rebuild.

```sh
docker network create hamalert-dev
docker run -d --name hamalert-dev-mongo --network hamalert-dev -p 127.0.0.1:27117:27017 docker.io/library/mongo:7

docker build -t hamalert-web-dev -f Dockerfile.dev .
docker run -d --name hamalert-dev-web --network hamalert-dev -p 127.0.0.1:8081:80 \
	--add-host=host.docker.internal:host-gateway \
	-v "$PWD":/var/www/html -v hamalert-dev-web-vendor:/var/www/html/vendor \
	-e MONGODB_URI=mongodb://hamalert-dev-mongo:27017/hamalert \
	-e SELF_URL=http://localhost:8081 \
	hamalert-web-dev

docker exec hamalert-dev-web php tools/seedLocalUser.php   # creates user HB9DQM / testpass123
```

Then open http://localhost:8081/login. On the first start the container runs `composer install`
(into the `hamalert-dev-web-vendor` volume, so your checkout stays clean) and generates
`config.inc.php` from `config_clean.inc.php`; both take a moment. Registration by e-mail does not
work locally, hence the seed script. Without the spot processing backend there are no spots and
no alerts, but everything else (login, trigger editor, destinations) works.

To tear it down: `docker rm -f hamalert-dev-web hamalert-dev-mongo`, and
`docker volume rm hamalert-dev-web-vendor` if you want Composer to start from scratch.

### Together with the backend

Clone [hamalert-server](https://github.com/hamalert/hamalert-server) next to this checkout and
run `npm run local-dev` there. It starts MongoDB, Redis, this web app (building the same image)
and the spot processing server, seeds a test user with triggers, and prints how to simulate
spots. See `LOCAL_DEV.md` in that repo. Only one of the two setups can run at a time, as they
use the same container names.

See `docker/dev/README.md` for the details of the dev image and its environment variables.
