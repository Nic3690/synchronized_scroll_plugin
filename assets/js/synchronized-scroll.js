(function($) {
    'use strict';
    
    $(document).ready(function() {
        initAllScrollContainers();
    });

    // Funzione per inizializzare tutti i container
    function initAllScrollContainers() {
        $('.sync-scroll-container').each(function() {
            initSynchronizedScroll($(this));
        });
    }

    // Funzione principale per inizializzare lo scorrimento sincronizzato
    function initSynchronizedScroll($container) {
        if ($container.length === 0) return;
        
        console.log('Initializing synchronized scroll for container:', $container.attr('id'));
        
        const $verticalContent = $container.find('.sync-vertical-content');
        const $horizontalContent = $container.find('.sync-horizontal-content');
        
        if ($verticalContent.length === 0 || $horizontalContent.length === 0) {
            console.error('Vertical or horizontal content not found');
            return;
        }

        // Ottieni le direzioni e la velocità dai data attributes
        const vDirection = $container.data('v-direction') || 'down';
        const hDirection = $container.data('h-direction') || 'right';
        const scrollSpeed = parseFloat($container.data('scroll-speed')) || 1.0;

        const vFactor = vDirection === 'up' ? -1 : 1;
        const hFactor = hDirection === 'left' ? -1 : 1;

        // Aspetta che le immagini siano caricate per calcolare correttamente le altezze
        $container.imagesLoaded().then(function() {
            calculateAndSetupScroll();
        });

        // Esegui il calcolo anche direttamente in caso non ci siano immagini
        calculateAndSetupScroll();

        // Funzione per calcolare dimensioni e configurare lo scorrimento
        function calculateAndSetupScroll() {
            // Calcola altezze e larghezze
            const verticalScrollHeight = $verticalContent.outerHeight() - $verticalContent.parent().outerHeight();
            const horizontalScrollWidth = $horizontalContent.outerWidth() - $horizontalContent.parent().outerWidth();
            const totalScrollHeight = $container[0].scrollHeight - $container.outerHeight();

            console.log('Vertical scroll height:', verticalScrollHeight);
            console.log('Horizontal scroll width:', horizontalScrollWidth);
            console.log('Total scroll height:', totalScrollHeight);

            // Verifica se ci sono dimensioni sufficienti per lo scorrimento
            if (verticalScrollHeight <= 0 || horizontalScrollWidth <= 0 || totalScrollHeight <= 0) {
                console.warn('Not enough content for scrolling in container:', $container.attr('id'));
                return;
            }

            // Imposta l'event handler per lo scorrimento
            $container.on('scroll', function() {
                const scrollPos = $container.scrollTop();
                const scrollPercentage = scrollPos / totalScrollHeight;
                
                // Applica la trasformazione verticale
                if (vDirection === 'up') {
                    $verticalContent.css('transform', `translateY(${vFactor * scrollPercentage * scrollSpeed * verticalScrollHeight}px)`);
                } else {
                    $verticalContent.css('transform', `translateY(-${scrollPercentage * scrollSpeed * verticalScrollHeight}px)`);
                }
                
                // Applica la trasformazione orizzontale
                if (hDirection === 'left') {
                    $horizontalContent.css('transform', `translateX(${hFactor * scrollPercentage * scrollSpeed * horizontalScrollWidth}px)`);
                } else {
                    $horizontalContent.css('transform', `translateX(-${scrollPercentage * scrollSpeed * horizontalScrollWidth}px)`);
                }
            });

            // Trigger iniziale per impostare la posizione
            $container.trigger('scroll');
        }
        
        // Ricalcola tutto al ridimensionamento della finestra
        $(window).on('resize', function() {
            setTimeout(calculateAndSetupScroll, 300);
        });
    }

    // Funzione da esporre globalmente per Elementor
    window.initSynchronizedScroll = initSynchronizedScroll;
    window.initAllScrollContainers = initAllScrollContainers;
    
    // Polyfill per imagesLoaded se non è disponibile
    $.fn.imagesLoaded = function() {
        const $this = this;
        const $images = $this.find('img');
        
        if ($images.length === 0) {
            return $.Deferred().resolve().promise();
        }
        
        const deferred = $.Deferred();
        let loaded = 0;
        
        $images.each(function() {
            const img = new Image();
            img.onload = img.onerror = function() {
                loaded++;
                if (loaded === $images.length) {
                    deferred.resolve();
                }
            };
            img.src = this.src;
        });
        
        return deferred.promise();
    };
    
})(jQuery);