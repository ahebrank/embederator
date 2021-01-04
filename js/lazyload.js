(function($, Drupal) {
  function loadEmbed(el) {
    var $embed = $(el);
    var entity_id = $embed.data('embederator-lazyload');
    var settings = $embed.data('embederator-settings');
    $embed.load(Drupal.url('embederator/lazyload/' + entity_id + '/' + settings));
  }

  function onIntersection(elements) {
    elements.forEach(el => {
      // Are we in viewport?
      if (el.intersectionRatio > 0) {
        // Stop watching.
        observer.unobserve(el.target);
        loadEmbed(el.target);
      }
    });
  }

  var $lazyelements = $('[data-embederator-lazyload]');

  if (!('IntersectionObserver' in window)) {
    // Load immediately if no intersection support.
    $lazyelements.each(function() {
      loadEmbed(this);
    });
  }
  else {
    var observer = new IntersectionObserver(onIntersection, {
      // Fuzzy to within 200px vertically.
      // @TODO: make this distance configurable (global setting for embederator?)
      rootMargin: '200px 0px',
      // Threshold is 'a ratio of intersection area to bounding box area of an observed target'.
      threshold: 0.01
    });
    $lazyelements.each(function() {
      observer.observe(this);
    });
  }
})(jQuery, Drupal);