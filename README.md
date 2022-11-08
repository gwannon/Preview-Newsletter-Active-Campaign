Este plugin se conecta a la API de Active Campaign y saca los últimas campañas que cumplan en el nombre alguna de las expresiones regualres (separadas por comas)
que contienen el shortocede [previewnewsletters]. Crea un grid con el asunto de la campaña y su imagen y lo enlaza a una previsialización  

Ejemplo de código corto:

[previewnewsletters]NEWSLETTER-MENSUAL-([0-9]*)-es,NEWSLETTER-SEMANAL-([0-9]*)-es[/previewnewsletters]

Mostrararía la últimas campañas enviadas que cumplieran estas expresiones regulares:

* NEWSLETTER-MENSUAL-([0-9]*)-es
* NEWSLETTER-SEMANAL-([0-9]*)-es

En WP-ADMIN > Ajustes > Preview Newsletter hay un formualrio donde configuramos la conexión a la API de Active Campaign.
