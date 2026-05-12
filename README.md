# CAS3 IAW GRUP 1 - Gestio de material Institut Montsia

Aquest README ensenya com posar en marxa el servidor.


## Primer pas

S'ha de clonar aquest repositori al servidor.
Clonar aquest git es fa amb aquesta comanda, "git clone https://github.com/javierbarberaher/Grup1_IAW.git", al ser un repositori public no et demanara login, el que requereix d'un token personal d'autentificació.


## Segon pas

Una vegada clonat el repositori, hi ha que enjegar-ho amb docker, en cas de no tenir docker, seguir aquest tutorial per instal·lar-lo: https://docs.docker.com/engine/install/ubuntu/
Si ja tenim docker, farem un cd a la carpeta del servidor i executarem "docker compose up -d --build", aixo escomençara a muntar els contenidors. Una vegada muntats ja tindrem el servidor en marxa.


## Navegant el servidor

Per entrar a la pàgina de login, ficarem la ip del servidor al nostre navegador, el que ens dirigirà directament a la pàgina de login. Per logearnos tenim aquestos usuaris per defecte:
```text
professor@iesmontsia.org / professor123
alumne@iesmontsia.org / alumne123
```

Si en lloc d'aixo volem administrar la base de dades, pots escriure la ip del servidor seguit de :8080 per accedir a phpmyadmin, les credencials son:
La ip del servidor com a l'apartata de servidor,
el usuari es root,
i ls contrasenya es root1234.
