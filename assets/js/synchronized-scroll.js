document.addEventListener('DOMContentLoaded', function() {
    console.log("Synchronized Scroll inizializzato");
    const scrollContainers = [];
    const debug = true; // Manteniamo attivo il debug
    
    // Attendiamo che il DOM sia completamente caricato
    setTimeout(function() {
        initScrollContainers();
    }, 500);
    
    function initScrollContainers() {
        // Usiamo sia la classe della vecchia versione che quella nuova per compatibilità
        const containers = document.querySelectorAll('.sync-scroll-yes, .sync-scroll-element');
        
        if (containers.length === 0) {
            console.log('Nessun container trovato con classe .sync-scroll-yes o .sync-scroll-element');
            return;
        }
        
        console.log('Trovati ' + containers.length + ' containers');
        
        containers.forEach(function(container) {
            // Controlliamo se il synchronize scroll è abilitato
            if (!container.getAttribute('data-sync-scroll') && !container.classList.contains('sync-scroll-yes')) {
                if (debug) console.log('Container non ha data-sync-scroll attribute', container);
                return;
            }
            
            let scrollType = container.getAttribute('data-scroll-type') || 'horizontal';
            
            // Supporto per approccio vecchio basato su classi
            if (!scrollType || scrollType === '') {
                if (container.classList.contains('sync-scroll-type-vertical')) {
                    scrollType = 'vertical';
                } else if (container.classList.contains('sync-scroll-type-parallax')) {
                    scrollType = 'parallax';
                } else {
                    scrollType = 'horizontal';
                }
            }
            
            let directionReverse = false;
            if (container.getAttribute('data-scroll-direction') === 'reverse' || 
                container.classList.contains('sync-scroll-direction-reverse')) {
                directionReverse = true;
            }
            
            const scrollSpeed = parseFloat(container.getAttribute('data-scroll-speed') || 1);
            
            console.log('Container configurato:', {
                type: scrollType,
                reverse: directionReverse,
                speed: scrollSpeed,
                container: container
            });
            
            // MODIFICA: Ora usiamo SEMPRE il container stesso come innerContainer
            let innerContainer = container;
            console.log('Usando il container stesso come inner container:', innerContainer);
            
            // Cerca tutti i widget all'interno del container
            const widgets = container.querySelectorAll('.elementor-widget');
            console.log(`Trovati ${widgets.length} widget nel container`);
            
            // Crea un wrapper per i widget
            setupContainerStyles(container, innerContainer, scrollType);
            
            scrollContainers.push({
                container: container,
                innerContainer: innerContainer,
                type: scrollType,
                reverse: directionReverse,
                speed: scrollSpeed,
                scrollWidth: 0,
                scrollHeight: 0,
                widgets: widgets,
                startPosition: 0, // Posizione iniziale (sempre 0)
                lastScrollProgress: 0 // Ultimo valore di scrollProgress calcolato
            });
        });
        
        if (scrollContainers.length > 0) {
            console.log('Attivo gli event listener di scroll');
            window.addEventListener('scroll', handlePageScroll);
            window.addEventListener('resize', function() {
                updateContainerDimensions();
                
                // Reset delle posizioni quando la finestra viene ridimensionata
                scrollContainers.forEach(function(item) {
                    const wrapper = item.container.querySelector('.sync-scroll-wrapper');
                    if (wrapper) {
                        wrapper.style.transform = 'translateY(0)';
                    }
                });
            });
            
            updateContainerDimensions();
            
            // Impostiamo esplicitamente tutti i wrapper a 0
            scrollContainers.forEach(function(item) {
                const wrapper = item.container.querySelector('.sync-scroll-wrapper');
                if (wrapper) {
                    wrapper.style.transform = item.type === 'horizontal' ? 
                        'translateX(0)' : 'translateY(0)';
                }
            });
        }
    }
    
    function updateContainerDimensions() {
        scrollContainers.forEach(function(item) {
            if (item.type === 'horizontal') {
                const wrapper = item.container.querySelector('.sync-scroll-wrapper');
                if (wrapper) {
                    const containerWidth = wrapper.scrollWidth;
                    const viewportWidth = item.container.offsetWidth;
                    item.scrollWidth = containerWidth - viewportWidth;
                    console.log(`Container orizzontale: larghezza = ${containerWidth}px, visibile = ${viewportWidth}px, scroll = ${item.scrollWidth}px`);
                }
            } else if (item.type === 'vertical') {
                const wrapper = item.container.querySelector('.sync-scroll-wrapper');
                if (wrapper) {
                    const containerHeight = wrapper.scrollHeight;
                    const viewportHeight = item.container.offsetHeight;
                    item.scrollHeight = containerHeight - viewportHeight;
                    console.log(`Container verticale: altezza = ${containerHeight}px, visibile = ${viewportHeight}px, scroll = ${item.scrollHeight}px`);
                }
            }
        });
    }
    
    function setupContainerStyles(container, innerContainer, scrollType) {
        // Proprietà importanti per tutti i tipi
        container.style.overflow = 'hidden'; // Cambiato da visible a hidden per evitare fuoriuscite
        
        // Crea un wrapper per i contenuti se non esiste già
        let wrapper = container.querySelector('.sync-scroll-wrapper');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'sync-scroll-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.willChange = 'transform';
            
            // Imposta il tempo di transizione in base all'attributo o al valore predefinito
            const transitionTime = container.getAttribute('data-scroll-transition') || '0.1';
            wrapper.style.transition = `transform ${transitionTime}s ease-out`;
            
            // Imposta la trasformazione iniziale a 0
            wrapper.style.transform = scrollType === 'horizontal' ? 'translateX(0)' : 'translateY(0)';
            
            // Impostazioni specifiche per tipo
            if (scrollType === 'horizontal') {
                wrapper.style.display = 'flex';
                wrapper.style.flexWrap = 'nowrap';
                
                // Se non abbiamo già impostato la larghezza tramite CSS, la impostiamo via JS
                const width = container.getAttribute('data-scroll-width') || '300%';
                wrapper.style.width = width;
                console.log(`Impostata larghezza a ${width} per container orizzontale`);
            } else if (scrollType === 'vertical' || scrollType === 'parallax') {
                // Se non abbiamo già impostato l'altezza tramite CSS, la impostiamo via JS
                const height = container.getAttribute('data-scroll-height') || '300%';
                wrapper.style.height = height;
                console.log(`Impostata altezza a ${height} per container vertical/parallax scroll`);
            }
            
            // Spostare tutti i figli nel wrapper
            while (container.firstChild) {
                wrapper.appendChild(container.firstChild);
            }
            
            container.appendChild(wrapper);
            console.log(`Creato wrapper per ${scrollType} scroll:`, wrapper);
        }
        
        console.log(`Container ${scrollType} configurato con successo`);
    }
    
    function handlePageScroll() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        
        scrollContainers.forEach(function(item) {
            const rect = item.container.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            // Il wrapper che contiene tutti i widget
            const wrapper = item.container.querySelector('.sync-scroll-wrapper');
            if (!wrapper) return;
            
            // Nuovo algoritmo di scroll progress migliorato:
            let scrollProgress = 0;
            
            // 1. Se il container non è ancora visibile (è sotto la viewport), scrollProgress = 0
            if (rect.top >= windowHeight) {
                scrollProgress = 0;
            }
            // 2. Se il container è completamente passato (è sopra la viewport), scrollProgress = 1
            else if (rect.bottom <= 0) {
                scrollProgress = 1;
            }
            // 3. Se il container è visibile (parzialmente o completamente)
            else {
                // Calcoliamo la percentuale in base a quanto è visibile
                scrollProgress = Math.min(1, Math.max(0, 
                    (windowHeight - rect.top) / (rect.height + windowHeight)
                ));
            }
            
            // IMPORTANTE: Se scrollTop è 0 (siamo all'inizio della pagina) e il container
            // è visibile all'inizio, forziamo scrollProgress = 0
            if (scrollTop === 0 && rect.top <= windowHeight && rect.top >= 0) {
                scrollProgress = 0;
            }
            
            // Salva l'ultimo valore di scrollProgress
            item.lastScrollProgress = scrollProgress;
            
            // Applica l'effetto in base al tipo con il directionFactor
            const directionFactor = item.reverse ? 1 : -1;
            
            if (item.type === 'horizontal') {
                // Per orizzontale usiamo una percentuale della larghezza del contenuto
                const maxScroll = item.scrollWidth;
                const translateX = directionFactor * scrollProgress * maxScroll * item.speed;
                console.log(`Container orizzontale: progress = ${scrollProgress.toFixed(2)}, translateX = ${translateX.toFixed(2)}px`);
                wrapper.style.transform = `translateX(${translateX}px)`;
            } else if (item.type === 'vertical') {
                // Per verticale usiamo una percentuale dell'altezza del contenuto
                const maxScroll = item.scrollHeight;
                
                // CRITICO: Se scrollProgress è 0, translation deve essere esattamente 0
                let translateY;
                if (scrollProgress === 0) {
                    translateY = 0;
                } else {
                    translateY = directionFactor * scrollProgress * maxScroll * item.speed;
                }
                
                console.log(`Container verticale: progress = ${scrollProgress.toFixed(2)}, translateY = ${translateY.toFixed(2)}px`);
                wrapper.style.transform = `translateY(${translateY}px)`;
            } else if (item.type === 'parallax') {
                const viewportCenter = windowHeight / 2;
                const elementCenter = rect.top + (rect.height / 2);
                const distance = viewportCenter - elementCenter;
                const maxDistance = windowHeight + rect.height;
                const parallaxProgress = distance / maxDistance * 2;
                
                // Anche qui, se scrollProgress è 0, forziamo translateY = 0
                let translateY;
                if (scrollProgress === 0) {
                    translateY = 0;
                } else {
                    translateY = directionFactor * parallaxProgress * 100 * item.speed;
                }
                
                console.log(`Container parallax: progress = ${parallaxProgress.toFixed(2)}, translateY = ${translateY.toFixed(2)}px`);
                wrapper.style.transform = `translateY(${translateY}px)`;
            }
        });
    }
});