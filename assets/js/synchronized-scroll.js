/**
 * Advanced Horizontal Scroll for Elementor
 * This script provides smooth horizontal scrolling functionality
 * that can be applied to any Elementor container
 */
(function($) {
    'use strict';

    const initializedContainers = new Set();

    $(document).ready(function() {
        initAllScrollContainers();

        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/section', function($element) {
                if ($element.hasClass('sync-scroll-yes')) {
                    initScrollContainer($element);
                }
            });
            
            elementorFrontend.hooks.addAction('frontend/element_ready/container', function($element) {
                if ($element.hasClass('sync-scroll-yes')) {
                    initScrollContainer($element);
                }
            });
        }
    });

    function initAllScrollContainers() {
        $('.sync-scroll-yes').each(function() {
            initScrollContainer($(this));
        });
    }

    function initScrollContainer($container) {
        const containerId = $container.attr('id') || 'sync-scroll-' + Math.random().toString(36).substr(2, 9);

        if (initializedContainers.has(containerId)) {
            return;
        }

        if (!$container.attr('id')) {
            $container.attr('id', containerId);
        }

        const settings = {
            type: $container.data('sync-scroll-type') || 'horizontal',
            direction: $container.data('sync-scroll-direction') || 'normal',
            speed: parseFloat($container.data('sync-scroll-speed') || 1),
            sticky: $container.hasClass('sync-scroll-sticky-yes'),
            overflow: $container.data('sync-scroll-overflow') || 'hidden'
        };
        
        console.log('Initializing scroll container:', containerId, settings);

        $container.addClass('sync-scroll-container-parent');

        const $contentContainer = $container.find('> .elementor-container, > .e-con-inner').first();
        
        if ($contentContainer.length === 0) {
            console.error('Content container not found for', containerId);
            return;
        }

        $contentContainer.addClass('sync-scroll-container');

        if (settings.type === 'horizontal') {
            setupHorizontalScroll($container, $contentContainer, settings);
        } else if (settings.type === 'vertical') {
            setupVerticalScroll($container, $contentContainer, settings);
        } else if (settings.type === 'parallax') {
            setupParallaxScroll($container, $contentContainer, settings);
        }
        initializedContainers.add(containerId);
    }

    function setupHorizontalScroll($container, $contentContainer, settings) {
        $container.css({
            'overflow-x': settings.overflow,
            'position': settings.sticky ? 'sticky' : 'relative',
            'top': settings.sticky ? '0' : 'auto',
        });

        $contentContainer.css({
            'display': 'flex',
            'flex-wrap': 'nowrap',
            'width': $container.data('sync-scroll-width') || '300%',
            'will-change': 'transform',
            'transition': `transform ${$container.data('sync-scroll-transition') || 0.2}s ease-out`
        });

        $contentContainer.children('.elementor-column, .elementor-widget, .e-con').css({
            'flex-shrink': '0',
            'width': 'auto'
        });

        const calculateDimensions = () => {
            const containerWidth = $contentContainer.outerWidth();
            const viewportWidth = window.innerWidth;
            const totalScrollWidth = containerWidth - viewportWidth;
            
            return {
                containerWidth,
                viewportWidth,
                totalScrollWidth
            };
        };
        
        let dimensions = calculateDimensions();
        const directionFactor = settings.direction === 'reverse' ? 1 : -1;

        const handleScroll = () => {
            const rect = $container[0].getBoundingClientRect();
            const windowHeight = window.innerHeight;

            if (rect.top < windowHeight && rect.bottom > 0) {
                const containerHeight = $container.outerHeight();
                const scrollableHeight = containerHeight;
                const scrollProgress = Math.min(1, Math.max(0, (windowHeight - rect.top) / scrollableHeight));

                const translateX = directionFactor * scrollProgress * dimensions.totalScrollWidth * settings.speed;
                $contentContainer.css('transform', `translateX(${translateX}px)`);
            }
        };

        $(window).on('scroll.syncScroll' + $container.attr('id'), handleScroll);
        $(window).on('resize.syncScroll' + $container.attr('id'), () => {
            dimensions = calculateDimensions();
            handleScroll();
        });

        setTimeout(handleScroll, 100);
    }

    function setupVerticalScroll($container, $contentContainer, settings) {
        $container.css({
            'overflow-y': settings.overflow,
            'position': settings.sticky ? 'sticky' : 'relative',
            'top': settings.sticky ? '0' : 'auto',
        });

        $contentContainer.css({
            'height': $container.data('sync-scroll-height') || '200%',
            'will-change': 'transform',
            'transition': `transform ${$container.data('sync-scroll-transition') || 0.2}s ease-out`
        });

        const calculateDimensions = () => {
            const containerHeight = $contentContainer.outerHeight();
            const viewportHeight = $container.outerHeight();
            const totalScrollHeight = containerHeight - viewportHeight;
            
            return {
                containerHeight,
                viewportHeight,
                totalScrollHeight
            };
        };
        
        let dimensions = calculateDimensions();
        const directionFactor = settings.direction === 'reverse' ? 1 : -1;

        const handleScroll = () => {
            const rect = $container[0].getBoundingClientRect();
            const windowHeight = window.innerHeight;

            if (rect.top < windowHeight && rect.bottom > 0) {
                const scrollableHeight = $container.outerHeight();
                const scrollProgress = Math.min(1, Math.max(0, (windowHeight - rect.top) / scrollableHeight));
                const translateY = directionFactor * scrollProgress * dimensions.totalScrollHeight * settings.speed;
                $contentContainer.css('transform', `translateY(${translateY}px)`);
            }
        };

        $(window).on('scroll.syncScroll' + $container.attr('id'), handleScroll);

        $(window).on('resize.syncScroll' + $container.attr('id'), () => {
            dimensions = calculateDimensions();
            handleScroll();
        });
        setTimeout(handleScroll, 100);
    }

    function setupParallaxScroll($container, $contentContainer, settings) {
        const directionFactor = settings.direction === 'reverse' ? 1 : -1;

        const handleScroll = () => {
            const rect = $container[0].getBoundingClientRect();
            const windowHeight = window.innerHeight;

            if (rect.top < windowHeight && rect.bottom > 0) {

                const viewportCenter = windowHeight / 2;
                const elementCenter = rect.top + ($container.outerHeight() / 2);
                const distance = viewportCenter - elementCenter;
                const maxDistance = windowHeight + $container.outerHeight();
                const scrollProgress = distance / maxDistance * 2;
                const translateY = directionFactor * scrollProgress * 100 * settings.speed; // 100px is the default parallax amount
                $contentContainer.css('transform', `translateY(${translateY}px)`);
            }
        };

        $(window).on('scroll.syncScroll' + $container.attr('id'), handleScroll);
        $(window).on('resize.syncScroll' + $container.attr('id'), handleScroll);

        setTimeout(handleScroll, 100);
    }

    window.syncScroll = {
        init: initScrollContainer,
        initAll: initAllScrollContainers
    };
    
})(jQuery);