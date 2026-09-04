(function (root, factory) {
  'use strict';

  var api = factory();

  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }

  if (root && root.document) {
    root.FLSNewsletterAnalytics = api;
    api.init(root);
  }
})(typeof window !== 'undefined' ? window : null, function () {
  'use strict';

  function parseNewsletterIdentity(pathname) {
    var segments = String(pathname || '').split('/').filter(Boolean);
    var slug = segments.length ? decodeURIComponent(segments[segments.length - 1]) : '';
    var match = /^(INT|EXT)-FLS-Newsletter-(Q[1-4]-\d{4})$/i.exec(slug);

    if (!match) {
      return null;
    }

    return {
      newsletter_slug: slug,
      newsletter_issue: match[2].toUpperCase(),
      newsletter_audience: match[1].toUpperCase(),
    };
  }

  function normalizeSectionName(value) {
    return String(value || '')
      .replace(/^#/, '')
      .replace(/^q[1-4]-/i, '')
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/^_+|_+$/g, '');
  }

  function publicUrl(url) {
    return url.protocol + '//' + url.host + url.pathname;
  }

  function classifyLink(href, pageUrl, options) {
    var rawHref = String(href || '').trim();
    var settings = options || {};
    var lowerHref = rawHref.toLowerCase();

    if (lowerHref.indexOf('mailto:') === 0) {
      return {
        event: 'newsletter_contact_click',
        params: { contact_method: 'email' },
      };
    }

    if (lowerHref.indexOf('tel:') === 0) {
      return {
        event: 'newsletter_contact_click',
        params: { contact_method: 'phone' },
      };
    }

    if (rawHref.charAt(0) === '#') {
      return {
        event: 'newsletter_nav_click',
        params: { newsletter_section: normalizeSectionName(rawHref) },
      };
    }

    var target;
    var current;
    try {
      current = new URL(pageUrl);
      target = new URL(rawHref, current);
    } catch (error) {
      return null;
    }

    if (target.protocol !== 'http:' && target.protocol !== 'https:') {
      return null;
    }

    var safeLink = {
      link_url: publicUrl(target),
      link_host: target.hostname,
    };

    if (settings.track === 'cta_click') {
      return {
        event: 'newsletter_cta_click',
        params: {
          cta_label: normalizeSectionName(settings.label),
          newsletter_section: normalizeSectionName(settings.section),
          link_url: safeLink.link_url,
          link_host: safeLink.link_host,
        },
      };
    }

    var fileMatch = /\/([^/]+\.(?:pdf|docx?|xlsx?|pptx?|zip|csv))$/i.exec(target.pathname);
    if (fileMatch) {
      return {
        event: 'newsletter_download',
        params: {
          link_url: safeLink.link_url,
          link_host: safeLink.link_host,
          file_name: decodeURIComponent(fileMatch[1]),
        },
      };
    }

    if (target.hostname !== current.hostname) {
      return {
        event: 'newsletter_outbound_click',
        params: safeLink,
      };
    }

    return null;
  }

  function classifyMarkedInteraction(dataset, sectionName) {
    var data = dataset || {};
    var section = normalizeSectionName(sectionName);

    if (data.flsTrack === 'video_play') {
      return {
        event: 'newsletter_video_play',
        params: {
          newsletter_section: section,
          video_name: normalizeSectionName(data.flsVideo || data.flsLabel || section),
        },
      };
    }

    if (data.flsTrack === 'carousel_interaction') {
      return {
        event: 'newsletter_carousel_interaction',
        params: {
          newsletter_section: section,
          carousel_name: normalizeSectionName(data.flsCarousel),
          carousel_item: normalizeSectionName(data.flsItem),
          interaction_action: normalizeSectionName(data.flsAction),
        },
      };
    }

    return null;
  }

  var ALLOWED_EVENTS = [
    'newsletter_view',
    'newsletter_scroll',
    'newsletter_section_view',
    'newsletter_nav_click',
    'newsletter_cta_click',
    'newsletter_video_play',
    'newsletter_carousel_interaction',
    'newsletter_download',
    'newsletter_contact_click',
    'newsletter_outbound_click',
  ];

  var ALLOWED_PARAMETERS = [
    'newsletter_issue',
    'newsletter_audience',
    'newsletter_slug',
    'newsletter_section',
    'scroll_percent',
    'cta_label',
    'interaction_action',
    'link_url',
    'link_host',
    'video_name',
    'carousel_name',
    'carousel_item',
    'file_name',
    'contact_method',
  ];

  var EVENT_SPECIFIC_PARAMETERS = ALLOWED_PARAMETERS.slice(3);

  function clearEventSpecificParameters(dataLayer) {
    var reset = {};
    EVENT_SPECIFIC_PARAMETERS.forEach(function (key) {
      reset[key] = undefined;
    });
    dataLayer.push(reset);
  }

  function buildEventPayload(eventName, identity, params) {
    if (ALLOWED_EVENTS.indexOf(eventName) === -1 || !identity) {
      return null;
    }

    var source = Object.assign({}, identity, params || {});
    var payload = { event: eventName };

    ALLOWED_PARAMETERS.forEach(function (key) {
      var value = source[key];
      if (value !== undefined && value !== null && value !== '') {
        payload[key] = value;
      }
    });

    return payload;
  }

  function createTracker(pathname, dataLayer) {
    var identity = parseNewsletterIdentity(pathname);
    var seen = new Set();

    return {
      identity: identity,
      emit: function (eventName, params, onceKey) {
        var dedupeKey = onceKey ? eventName + ':' + onceKey : '';
        if (dedupeKey && seen.has(dedupeKey)) {
          return false;
        }

        var payload = buildEventPayload(eventName, identity, params);
        if (!payload) {
          return false;
        }

        if (dedupeKey) {
          seen.add(dedupeKey);
        }
        clearEventSpecificParameters(dataLayer);
        dataLayer.push(payload);
        return true;
      },
    };
  }

  function getNewScrollThresholds(percent, sent) {
    var thresholds = [25, 50, 75, 90];
    return thresholds.filter(function (threshold) {
      if (percent < threshold || sent.has(threshold)) {
        return false;
      }
      sent.add(threshold);
      return true;
    });
  }

  function init(root) {
    if (!root || !root.document || root.__flsNewsletterAnalyticsInitialized) {
      return null;
    }

    root.dataLayer = root.dataLayer || [];
    var tracker = createTracker(root.location.pathname, root.dataLayer);
    if (!tracker.identity) {
      return null;
    }
    root.__flsNewsletterAnalyticsInitialized = true;

    function start() {
      tracker.emit('newsletter_view', {}, 'page');

      var sentScroll = new Set();
      function checkScroll() {
        var bodyHeight = root.document.body ? root.document.body.scrollHeight : 0;
        var documentHeight = Math.max(root.document.documentElement.scrollHeight || 0, bodyHeight);
        if (!documentHeight) {
          return;
        }
        var percent = Math.min(100, ((root.scrollY + root.innerHeight) / documentHeight) * 100);
        getNewScrollThresholds(percent, sentScroll).forEach(function (threshold) {
          tracker.emit('newsletter_scroll', { scroll_percent: threshold }, String(threshold));
        });
      }

      root.addEventListener('scroll', checkScroll, { passive: true });
      checkScroll();

      if (typeof root.IntersectionObserver === 'function') {
        var sectionObserver = new root.IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
              return;
            }
            var section = normalizeSectionName(entry.target.dataset.flsSection);
            if (section) {
              tracker.emit(
                'newsletter_section_view',
                { newsletter_section: section },
                section
              );
            }
            sectionObserver.unobserve(entry.target);
          });
        }, {
          rootMargin: '-20% 0px -20% 0px',
          threshold: [0],
        });

        Array.prototype.forEach.call(
          root.document.querySelectorAll('[data-fls-section]'),
          function (section) {
            sectionObserver.observe(section);
          }
        );
      }

      root.document.addEventListener('click', function (clickEvent) {
        if (!clickEvent.target || typeof clickEvent.target.closest !== 'function') {
          return;
        }

        var element = clickEvent.target.closest('[data-fls-track], a[href]');
        if (!element) {
          return;
        }

        var sectionElement = typeof element.closest === 'function'
          ? element.closest('[data-fls-section]')
          : null;
        var sectionName = element.dataset.flsSection || (sectionElement && sectionElement.dataset.flsSection);
        var markedResult = classifyMarkedInteraction(element.dataset, sectionName);
        if (markedResult) {
          tracker.emit(markedResult.event, markedResult.params);
          return;
        }

        if (String(element.tagName).toUpperCase() !== 'A') {
          return;
        }

        var result = classifyLink(
          element.getAttribute('href'),
          root.location.href,
          {
            track: element.dataset.flsTrack,
            label: element.dataset.flsLabel,
            section: sectionName,
          }
        );

        if (result) {
          tracker.emit(result.event, result.params);
        }
      });
    }

    if (root.document.readyState === 'loading') {
      root.document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
      start();
    }

    return tracker;
  }

  return {
    buildEventPayload: buildEventPayload,
    classifyLink: classifyLink,
    classifyMarkedInteraction: classifyMarkedInteraction,
    createTracker: createTracker,
    getNewScrollThresholds: getNewScrollThresholds,
    init: init,
    normalizeSectionName: normalizeSectionName,
    parseNewsletterIdentity: parseNewsletterIdentity,
  };
});
