define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $container = $(element);
        var $items = $container.find('.faq-item');
        var $search = $container.find('#faq-search');
        var $catBtns = $container.find('.faq-cat-btn');
        var $noResult = $container.find('#no-faq-found');

        // 1. Accordion functionality
        $container.on('click', '.faq-question', function () {
            var $currentItem = $(this).closest('.faq-item');
            var $currentAnswer = $currentItem.find('.faq-answer');
            var $currentIcon = $(this).find('.faq-icon');

            if ($currentItem.hasClass('active')) {
                $currentItem.removeClass('active');
                $currentAnswer.slideUp(200);
                $currentIcon.text('+');
            } else {
                $items.removeClass('active');
                $container.find('.faq-answer').slideUp(200);
                $container.find('.faq-icon').text('+');

                $currentItem.addClass('active');
                $currentAnswer.slideDown(200);
                $currentIcon.text('-');
            }
        });

        // 2. Category Filtering & Search Filtering Logic
        function filterFaqs() {
            var searchQuery = $search.val().toLowerCase();
            var activeCat = $container.find('.faq-cat-btn.active').data('category');
            var visibleCount = 0;

            $items.each(function () {
                var $item = $(this);
                var category = $item.data('category');
                var text = $item.text().toLowerCase();

                var matchesCat = (activeCat === 'all' || category === activeCat);
                var matchesSearch = text.indexOf(searchQuery) > -1;

                if (matchesCat && matchesSearch) {
                    $item.show();
                    visibleCount++;
                } else {
                    $item.hide();
                    $item.removeClass('active');
                    $item.find('.faq-answer').hide();
                    $item.find('.faq-icon').text('+');
                }
            });

            if (visibleCount === 0) {
                $noResult.show();
            } else {
                $noResult.hide();
            }
        }

        // Bind Search Input Event
        $search.on('keyup', function () {
            filterFaqs();
        });

        // Bind Category Button Click Event (Fixed syntax)
        $catBtns.on('click', function () {
            $catBtns.removeClass('active');
            $(this).addClass('active');
            filterFaqs();
        });
    };
});
