document.addEventListener('DOMContentLoaded', function() {
    const scrollContainers = [];
    
    initScrollContainers();
    
    function initScrollContainers() {
        const containers = document.querySelectorAll('.sync-scroll-yes');
        
        if (containers.length === 0) {
            return;
        }
        
        containers.forEach(function(container) {
            const scrollType = container.classList.contains('sync-scroll-type-vertical') ? 'vertical' : 
                               container.classList.contains('sync-scroll-type-parallax') ? 'parallax' : 'horizontal';
            
            const directionReverse = container.classList.contains('sync-scroll-direction-reverse');
            const scrollSpeed = parseFloat(container.dataset.scrollSpeed || container.getAttribute('data-sync-scroll-speed') || 1);
            
            const innerContainer = container.querySelector('.elementor-container, .e-con-inner');
            
            if (!innerContainer) {
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
            } else if (item.type === 'vertical') {
                const containerHeight = item.innerContainer.scrollHeight;
                const viewportHeight = item.container.offsetHeight;
                item.scrollHeight = containerHeight - viewportHeight;
            }
        });
    }
    
    function setupContainerStyles(container, innerContainer, scrollType) {
        container.style.overflow = 'visible';
        
        if (scrollType === 'horizontal') {
            innerContainer.style.display = 'flex';
            innerContainer.style.flexWrap = 'nowrap';
            innerContainer.style.width = '300%';
            innerContainer.style.willChange = 'transform';
            innerContainer.style.transition = 'transform 0.1s ease-out';
            
            Array.from(innerContainer.children).forEach(function(child) {
                child.style.flexShrink = '0';
                child.style.width = 'auto';
            });
        } else if (scrollType === 'vertical') {
            innerContainer.style.height = '200%';
            innerContainer.style.willChange = 'transform';
            innerContainer.style.transition = 'transform 0.1s ease-out';
        } else if (scrollType === 'parallax') {
            innerContainer.style.willChange = 'transform';
            innerContainer.style.transition = 'transform 0.1s ease-out';
        }
    }
    
    function handlePageScroll() {
        scrollContainers.forEach(function(item) {
            const rect = item.container.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            if (rect.top < windowHeight && rect.bottom > 0) {
                const containerHeight = item.container.offsetHeight;
                const scrollProgress = Math.min(1, Math.max(0, 
                    (windowHeight - rect.top) / (containerHeight + windowHeight)
                ));
                
                const directionFactor = item.reverse ? 1 : -1;
                
                if (item.type === 'horizontal') {
                    const translateX = directionFactor * scrollProgress * item.scrollWidth * item.speed;
                    item.innerContainer.style.transform = `translateX(${translateX}px)`;
                } else if (item.type === 'vertical') {
                    const translateY = directionFactor * scrollProgress * item.scrollHeight * item.speed;
                    item.innerContainer.style.transform = `translateY(${translateY}px)`;
                } else if (item.type === 'parallax') {
                    const viewportCenter = windowHeight / 2;
                    const elementCenter = rect.top + (containerHeight / 2);
                    const distance = viewportCenter - elementCenter;
                    const maxDistance = windowHeight + containerHeight;
                    const parallaxProgress = distance / maxDistance * 2;
                    
                    const translateY = directionFactor * parallaxProgress * 100 * item.speed;
                    item.innerContainer.style.transform = `translateY(${translateY}px)`;
                }
            }
        });
    }
});