document.addEventListener('DOMContentLoaded', function() {
    console.log("Synchronized Scroll inizializzato");
    const scrollContainers = [];
    const debug = true; // Lasciamo attivo il debug 
    
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
            
            const innerContainer = container.querySelector('.elementor-container, .e-con-inner');
            
            if (!innerContainer) {
                console.error('Inner container non trovato nel container:', container);
                return;
            }
            
            setupContainerStyles(container, innerContainer, scrollType);
            
            scrollContainers.push({
                container: container,
                innerContainer: innerContainer,
                type: scrollType,
                reverse: directionReverse,
                speed: scrollSpeed,
                scrollWidth: 0,
                scrollHeight: 0
            });
        });
        
        if (scrollContainers.length > 0) {
            console.log('Attivo gli event listener di scroll');
            window.addEventListener('scroll', handlePageScroll);
            window.addEventListener('resize', updateContainerDimensions);
            updateContainerDimensions();
            handlePageScroll();
        }
    }
    
    function updateContainerDimensions() {
        scrollContainers.forEach(function(item) {
            if (item.type === 'horizontal') {
                const containerWidth = item.innerContainer.scrollWidth;
                const viewportWidth = window.innerWidth;
                item.scrollWidth = containerWidth - viewportWidth;
                console.log(`Container orizzontale: larghezza = ${containerWidth}px, visibile = ${viewportWidth}px, scroll = ${item.scrollWidth}px`);
            } else if (item.type === 'vertical') {
                const containerHeight = item.innerContainer.scrollHeight;
                const viewportHeight = item.container.offsetHeight;
                item.scrollHeight = containerHeight - viewportHeight;
                console.log(`Container verticale: altezza = ${containerHeight}px, visibile = ${viewportHeight}px, scroll = ${item.scrollHeight}px`);
            }
        });
    }
    
    function setupContainerStyles(container, innerContainer, scrollType) {
        // Proprietà importanti per tutti i tipi
        container.style.overflow = 'visible'; 
        innerContainer.style.willChange = 'transform';
        innerContainer.style.transition = 'transform 0.1s ease-out';
        
        if (scrollType === 'horizontal') {
            innerContainer.style.display = 'flex';
            innerContainer.style.flexWrap = 'nowrap';
            
            // Se non abbiamo già impostato la larghezza tramite CSS, la impostiamo via JS
            if (!innerContainer.style.width || innerContainer.style.width === '') {
                const width = container.getAttribute('data-scroll-width') || '300%';
                innerContainer.style.width = width;
                console.log(`Impostata larghezza a ${width} per container orizzontale`);
            }
            
            Array.from(innerContainer.children).forEach(function(child) {
                child.style.flexShrink = '0';
                child.style.width = 'auto';
            });
        } else if (scrollType === 'vertical') {
            // Se non abbiamo già impostato l'altezza tramite CSS, la impostiamo via JS
            if (!innerContainer.style.height || innerContainer.style.height === '') {
                const height = container.getAttribute('data-scroll-height') || '200%';
                innerContainer.style.height = height;
                console.log(`Impostata altezza a ${height} per container verticale`);
            }
        }
        
        console.log(`Container ${scrollType} configurato con successo`);
    }
    
    function handlePageScroll() {
        scrollContainers.forEach(function(item) {
            const rect = item.container.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            // Controlliamo se il container è visibile
            if (rect.top < windowHeight && rect.bottom > 0) {
                const containerHeight = item.container.offsetHeight;
                
                // Calcola la percentuale di scrolling
                const scrollProgress = Math.min(1, Math.max(0, 
                    (windowHeight - rect.top) / (containerHeight + windowHeight)
                ));
                
                // Applica l'effetto in base al tipo
                const directionFactor = item.reverse ? 1 : -1;
                
                if (item.type === 'horizontal') {
                    const translateX = directionFactor * scrollProgress * item.scrollWidth * item.speed;
                    console.log(`Container orizzontale: progress = ${scrollProgress.toFixed(2)}, translateX = ${translateX.toFixed(2)}px`);
                    item.innerContainer.style.transform = `translateX(${translateX}px)`;
                } else if (item.type === 'vertical') {
                    // Per il verticale usiamo un fattore moltiplicativo più grande
                    const factor = item.container.offsetHeight;
                    const translateY = directionFactor * scrollProgress * factor * item.speed;
                    console.log(`Container verticale: progress = ${scrollProgress.toFixed(2)}, translateY = ${translateY.toFixed(2)}px`);
                    item.innerContainer.style.transform = `translateY(${translateY}px)`;
                } else if (item.type === 'parallax') {
                    const viewportCenter = windowHeight / 2;
                    const elementCenter = rect.top + (containerHeight / 2);
                    const distance = viewportCenter - elementCenter;
                    const maxDistance = windowHeight + containerHeight;
                    const parallaxProgress = distance / maxDistance * 2;
                    
                    const translateY = directionFactor * parallaxProgress * 100 * item.speed;
                    console.log(`Container parallax: progress = ${parallaxProgress.toFixed(2)}, translateY = ${translateY.toFixed(2)}px`);
                    item.innerContainer.style.transform = `translateY(${translateY}px)`;
                }
            }
        });
    }
});