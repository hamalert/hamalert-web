# hamalert-web dev Docker image

This directory holds the pieces for `Dockerfile.dev` at the repo root: a
`php:8.3-apache` image for running this app locally against a dev MongoDB and
the other pieces of a local HamAlert stack (spot simulator, matcher, etc.),
without needing PHP/Apache/the mongodb extension installed on the host.

- `apache.conf` — reproduces, for local dev, the root-level extensionless-URL
  rewrite (`/triggers` -> `triggers.php`, etc.) that production gets from
  webserver config outside this repo. See the comments in that file and in
  `../../tools/router.php` for the exact behaviour being replicated.
- `entrypoint.sh` — one-time setup that has to happen inside the (bind-
  mounted) checkout on each container start: `composer install` if
  `vendor/mongodb` is missing, and generating `config.inc.php` from
  `config_clean.inc.php` if it doesn't exist yet.

The image does **not** contain the app source — it's bind-mounted at
`/var/www/html` when the container is run, so edits on the host take effect
immediately (no rebuild needed for PHP/JS/CSS changes; rebuild only if
`Dockerfile.dev` itself or the base image's extensions need to change).

## Normal usage

From the sibling `hamalert-server` repo, `npm run local-dev` builds this
image and starts a container from it (along with the dev MongoDB, spot
simulator, etc.) as part of the local dev stack. That's the intended way to
run this day-to-day — see that repo for the full stack.

## Running standalone

To build and run just this image, against a MongoDB you already have running
and reachable:

```sh
docker build -t hamalert-web-dev -f Dockerfile.dev .

docker run -d --name hamalert-dev-web \
	-p 127.0.0.1:8081:80 \
	--add-host=host.docker.internal:host-gateway \
	-v "$PWD":/var/www/html \
	-e MONGODB_URI=mongodb://host.docker.internal:27017/hamalert \
	-e SELF_URL=http://localhost:8081 \
	-e SIMULATE_SPOT_URL=http://host.docker.internal:1983/sendSpot \
	hamalert-web-dev
```

Then open http://localhost:8081/login.

Environment variables the entrypoint reads (only used the first time, to
generate `config.inc.php` — see `entrypoint.sh`; delete `config.inc.php` in
your checkout to have it regenerated on the next container start):

| Variable             | Default (if unset)                              | Used for `config.inc.php` key(s)                    |
|----------------------|---------------------------------------------------|------------------------------------------------------|
| `MONGODB_URI`        | `mongodb://hamalert-dev-mongo:27017/hamalert`     | `mongodb_uri`                                         |
| `SELF_URL`           | `http://localhost:8081`                           | `self_url`                                            |
| `SIMULATE_SPOT_URL`  | `http://host.docker.internal:1983/sendSpot`       | `simulate_spot_url`, `simulate_spot_url_test`         |

`composer install` and `config.inc.php` generation write into your working
copy (because the source is bind-mounted) — both `vendor/` (beyond the parts
already committed) and `config.inc.php` are gitignored, so this doesn't dirty
`git status`.
