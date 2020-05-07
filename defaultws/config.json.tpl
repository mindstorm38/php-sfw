{
	"global": {
		"secure": {SECURE},
		"advised_host": "{ADVISED_HOST}",
		"base_path": ""
	},
	"prototype": {
		"enabled": false,
		"users": {
			"guest": ""
		}
	},
	"database": {
		"host": "127.0.0.1:3306",
		"user": "root",
		"charset": "utf8",
		"password": "",
		"name": ""
	},
	"session": {
		"cookie": "SFWSESSID",
		"regenerate_interval": 900,
		"configs": {
			"default": {
				"lifetime": 604800,
				"auto_update": true
			}
		}
	},
	"jwt": {
		"secret": "%{JWT_SECRET}%",
		"algorithm": "HS256"
	}
}
