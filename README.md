### New Laravel Project Installer ###
---

Easily create a new [Laravel](https://www.laravel.com) project of any version.  Additionally, you can also optionally install composer packages at the same time the project is created.

---
#### Installation

`composer -g install patinthehat/new-laravel-project`

---
#### Sample Usage

Create a project with the latest Laravel version:
	`new-laravel-project myproject`

Create a Laravel 5.3 project:
	`new-laravel-project myproject 5.3`
	
Create a Laravel 5.4 project with optional packages:
	`new-laravel-project myproject 5.4 --packages=laracasts/flash,guzzlehttp/guzzle`

---
#### References
Portions of this code were borrowed from the laravel installer, found at https://github.com/laravel/installer.

---
#### Notes
This uses `composer create-project` to create the project skeleton, not the `laravel new` command.

---
#### License
This project is licensed under the [MIT License](LICENSE).