=== Synchronized Scroll ===
Contributors: devxx
Tags: scroll, synchronized, vertical, horizontal, elementor
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Crea contenitori con scorrimento sincronizzato verticale e orizzontale, con controllo completo su direzione, altezza e velocità.

== Description ==

Il plugin Synchronized Scroll ti permette di creare facilmente container con scorrimento sincronizzato. Con un solo scroll puoi muovere contemporaneamente contenuti in direzione verticale e orizzontale.

Caratteristiche principali:

* Scorrimento sincronizzato tra contenuti verticali e orizzontali
* Controllo completo sulla direzione di scorrimento (alto/basso, sinistra/destra)
* Personalizzazione delle altezze per ogni container
* Regolazione della velocità di scorrimento
* Compatibilità con Elementor e altri page builder
* Supporto responsive per dispositivi mobili
* Nasconde le barre di scorrimento secondarie mantenendo solo quella principale

Questo plugin è ideale per:

* Presentazioni creative
* Portfolio interattivi
* Siti web di design
* Landing page
* Showcase di prodotti

= Utilizzo =

Inserisci lo shortcode [synchronized_scroll] in qualsiasi pagina:

`
[synchronized_scroll]
    [vertical_content]
        <h2>Il tuo contenuto verticale</h2>
        <p>Inserisci qui il testo che vuoi far scorrere verticalmente...</p>
        <!-- Aggiungi più contenuto qui -->
    [/vertical_content]
    
    [horizontal_content]
        <div class="item">
            <h3>Elemento 1</h3>
            <p>Descrizione 1</p>
        </div>
        <div class="item">
            <h3>Elemento 2</h3>
            <p>Descrizione 2</p>
        </div>
        <!-- Aggiungi più elementi qui -->
    [/horizontal_content]
[/synchronized_scroll]
`

= Personalizzazione =

Puoi personalizzare lo shortcode con vari parametri:

`
[synchronized_scroll 
    vertical_height="60vh" 
    horizontal_height="40vh" 
    mobile_height="350px" 
    breakpoint="576px" 
    horizontal_width="400%" 
    v_direction="up" 
    h_direction="left" 
    scroll_speed="1.5"
    transition="0.2s"]
    ...contenuto...
[/synchronized_scroll]
`

* vertical_height: Altezza del container verticale (default: 50vh)
* horizontal_height: Altezza del container orizzontale (default: 50vh)
* mobile_height: Altezza del container verticale su mobile (default: 400px)
* breakpoint: Punto di breakpoint per il cambio a mobile (default: 769px)
* horizontal_width: Larghezza del contenuto orizzontale (default: 300%)
* v_direction: Direzione di scorrimento verticale, "up" o "down" (default: down)
* h_direction: Direzione di scorrimento orizzontale, "left" o "right" (default: right)
* scroll_speed: Velocità di scorrimento, numero decimale (default: 1.0)
* transition: Durata della transizione smooth in secondi (default: 0.05s)

== Installation ==

1. Carica il file zip del plugin dalla dashboard di WordPress
2. Vai su "Plugin" → "Aggiungi nuovo" → "Carica plugin"
3. Seleziona il file zip e clicca su "Installa ora"
4. Attiva il plugin dalla pagina dei plugin

In alternativa:

1. Estrai il file zip nella cartella `/wp-content/plugins/`
2. Attiva il plugin dalla pagina dei plugin di WordPress

== Frequently Asked Questions ==

= Lo shortcode funziona con Elementor? =

Sì, puoi inserire lo shortcode all'interno di un widget HTML di Elementor.

= Posso cambiare i colori dei container? =

Sì, puoi personalizzare i colori e lo stile aggiungendo CSS personalizzato al tuo tema.

= È possibile usare più di una istanza nella stessa pagina? =

Sì, puoi usare lo shortcode più volte nella stessa pagina, ogni istanza avrà un ID unico e impostazioni indipendenti.

= Posso invertire la direzione di scorrimento? =

Sì, usa i parametri v_direction="up" e h_direction="left" per invertire le direzioni di scorrimento predefinite.

= Come posso velocizzare o rallentare lo scorrimento? =

Utilizza il parametro scroll_speed con valori maggiori di 1.0 per accelerare o minori di 1.0 per rallentare.

== Screenshots ==

1. Esempio di scorrimento sincronizzato in azione
2. Versione mobile del plugin
3. Integrazione con Elementor

== Changelog ==

= 1.0.0 =
* Prima versione

== Upgrade Notice ==

= 1.0.0 =
Prima versione del plugin, non ci sono aggiornamenti disponibili.