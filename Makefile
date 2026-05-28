up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

shell:
	docker compose exec app bash

fresh:
	docker compose exec app php artisan migrate:fresh --seed

rebuild:
	docker compose down && docker compose up -d --build
