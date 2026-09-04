'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const tracking = require('../assets/js/fls-newsletter-tracking.js');

test('parses the INT Q3 newsletter identity from its exact slug', () => {
  assert.deepEqual(
    tracking.parseNewsletterIdentity('/INT-FLS-Newsletter-Q3-2026/'),
    {
      newsletter_slug: 'INT-FLS-Newsletter-Q3-2026',
      newsletter_issue: 'Q3-2026',
      newsletter_audience: 'INT',
    }
  );
});

test('parses the EXT Q3 newsletter identity from its exact slug', () => {
  assert.deepEqual(
    tracking.parseNewsletterIdentity('/EXT-FLS-Newsletter-Q3-2026/'),
    {
      newsletter_slug: 'EXT-FLS-Newsletter-Q3-2026',
      newsletter_issue: 'Q3-2026',
      newsletter_audience: 'EXT',
    }
  );
});

test('supports future quarterly newsletters without changing GTM', () => {
  assert.deepEqual(
    tracking.parseNewsletterIdentity('/INT-FLS-Newsletter-Q1-2027/'),
    {
      newsletter_slug: 'INT-FLS-Newsletter-Q1-2027',
      newsletter_issue: 'Q1-2027',
      newsletter_audience: 'INT',
    }
  );
});

test('does not treat unrelated URLs as a newsletter identity', () => {
  assert.equal(tracking.parseNewsletterIdentity('/contact/'), null);
});

test('classifies email links without sending the email address', () => {
  assert.deepEqual(
    tracking.classifyLink('mailto:person@example.com', 'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/'),
    {
      event: 'newsletter_contact_click',
      params: { contact_method: 'email' },
    }
  );
});

test('classifies telephone links without sending the phone number', () => {
  assert.deepEqual(
    tracking.classifyLink('tel:+84123456789', 'https://newsletter.fls-group.com/EXT-FLS-Newsletter-Q3-2026/'),
    {
      event: 'newsletter_contact_click',
      params: { contact_method: 'phone' },
    }
  );
});

test('classifies an in-page Q3 navigation link semantically', () => {
  assert.deepEqual(
    tracking.classifyLink('#q3-special-news', 'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/'),
    {
      event: 'newsletter_nav_click',
      params: { newsletter_section: 'special_news' },
    }
  );
});

test('strips query strings and fragments from outbound link tracking', () => {
  assert.deepEqual(
    tracking.classifyLink(
      'https://partner.example.com/case-study?email=person%40example.com#bio',
      'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/'
    ),
    {
      event: 'newsletter_outbound_click',
      params: {
        link_url: 'https://partner.example.com/case-study',
        link_host: 'partner.example.com',
      },
    }
  );
});

test('a marked external CTA emits only the CTA event', () => {
  assert.deepEqual(
    tracking.classifyLink(
      'https://fls-group.com/contact/?source=newsletter',
      'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/',
      { track: 'cta_click', label: 'contact_team', section: 'leadership_message' }
    ),
    {
      event: 'newsletter_cta_click',
      params: {
        cta_label: 'contact_team',
        newsletter_section: 'leadership_message',
        link_url: 'https://fls-group.com/contact/',
        link_host: 'fls-group.com',
      },
    }
  );
});

test('classifies a file link as a newsletter download', () => {
  assert.deepEqual(
    tracking.classifyLink(
      '/downloads/FLS-Q3-2026.pdf?download=1',
      'https://newsletter.fls-group.com/EXT-FLS-Newsletter-Q3-2026/'
    ),
    {
      event: 'newsletter_download',
      params: {
        link_url: 'https://newsletter.fls-group.com/downloads/FLS-Q3-2026.pdf',
        link_host: 'newsletter.fls-group.com',
        file_name: 'FLS-Q3-2026.pdf',
      },
    }
  );
});

test('builds an allowlisted GA4 event payload with Q3 identity', () => {
  assert.deepEqual(
    tracking.buildEventPayload(
      'newsletter_scroll',
      tracking.parseNewsletterIdentity('/INT-FLS-Newsletter-Q3-2026/'),
      { scroll_percent: 50, email: 'must-not-be-sent@example.com', link_url: '' }
    ),
    {
      event: 'newsletter_scroll',
      newsletter_issue: 'Q3-2026',
      newsletter_audience: 'INT',
      newsletter_slug: 'INT-FLS-Newsletter-Q3-2026',
      scroll_percent: 50,
    }
  );
});

test('rejects events outside the fixed newsletter event contract', () => {
  assert.equal(
    tracking.buildEventPayload(
      'generate_lead',
      tracking.parseNewsletterIdentity('/INT-FLS-Newsletter-Q3-2026/'),
      {}
    ),
    null
  );
});

test('tracker pushes a once-only event to the dataLayer', () => {
  const dataLayer = [];
  const tracker = tracking.createTracker('/EXT-FLS-Newsletter-Q3-2026/', dataLayer);

  tracker.emit('newsletter_view', {}, 'view');
  tracker.emit('newsletter_view', {}, 'view');

  const events = dataLayer.filter((item) => item.event);
  assert.equal(events.length, 1);
  assert.deepEqual(events[0], {
    event: 'newsletter_view',
    newsletter_issue: 'Q3-2026',
    newsletter_audience: 'EXT',
    newsletter_slug: 'EXT-FLS-Newsletter-Q3-2026',
  });
});

test('tracker clears optional event parameters before each dataLayer event', () => {
  const dataLayer = [];
  const tracker = tracking.createTracker('/INT-FLS-Newsletter-Q3-2026/', dataLayer);

  tracker.emit('newsletter_cta_click', {
    cta_label: 'contact_team',
    link_host: 'fls-group.com',
  });
  tracker.emit('newsletter_video_play', { video_name: 'breakbulk_event' });

  const state = {};
  const eventSnapshots = [];
  dataLayer.forEach((item) => {
    Object.assign(state, item);
    if (item.event) {
      eventSnapshots.push(Object.assign({}, state));
    }
  });

  const videoEvent = eventSnapshots.find((item) => item.event === 'newsletter_video_play');
  assert.equal(videoEvent.video_name, 'breakbulk_event');
  assert.equal(videoEvent.cta_label, undefined);
  assert.equal(videoEvent.link_host, undefined);
});

test('scroll threshold helper reports each newly reached threshold once', () => {
  const sent = new Set([25]);
  assert.deepEqual(tracking.getNewScrollThresholds(76, sent), [50, 75]);
});

test('browser init emits the Q3 view and reached scroll events', () => {
  const listeners = {};
  const document = {
    readyState: 'complete',
    body: { scrollHeight: 1000 },
    documentElement: { scrollHeight: 1000 },
    addEventListener(name, handler) {
      listeners['document:' + name] = handler;
    },
    querySelectorAll() {
      return [];
    },
  };
  const win = {
    document,
    location: {
      href: 'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/',
      pathname: '/INT-FLS-Newsletter-Q3-2026/',
    },
    dataLayer: [],
    innerHeight: 100,
    scrollY: 0,
    addEventListener(name, handler) {
      listeners['window:' + name] = handler;
    },
  };

  tracking.init(win);
  assert.equal(win.dataLayer.find((item) => item.event).event, 'newsletter_view');

  win.scrollY = 400;
  listeners['window:scroll']();
  assert.deepEqual(
    win.dataLayer
      .filter((item) => item.event)
      .map((item) => [item.event, item.scroll_percent || null]),
    [
      ['newsletter_view', null],
      ['newsletter_scroll', 25],
      ['newsletter_scroll', 50],
    ]
  );
});

test('section visibility emits once from semantic data attributes', () => {
  let observerCallback;
  const observed = [];
  const section = { dataset: { flsSection: 'leadership_message' } };
  const document = {
    readyState: 'complete',
    body: { scrollHeight: 1000 },
    documentElement: { scrollHeight: 1000 },
    addEventListener() {},
    querySelectorAll(selector) {
      return selector === '[data-fls-section]' ? [section] : [];
    },
  };
  const win = {
    document,
    location: {
      href: 'https://newsletter.fls-group.com/EXT-FLS-Newsletter-Q3-2026/',
      pathname: '/EXT-FLS-Newsletter-Q3-2026/',
    },
    dataLayer: [],
    innerHeight: 100,
    scrollY: 0,
    addEventListener() {},
    IntersectionObserver: class {
      constructor(callback) {
        observerCallback = callback;
      }
      observe(element) {
        observed.push(element);
      }
      unobserve() {}
    },
  };

  tracking.init(win);
  assert.deepEqual(observed, [section]);

  observerCallback([{ target: section, isIntersecting: true, intersectionRatio: 0.6 }]);
  observerCallback([{ target: section, isIntersecting: true, intersectionRatio: 0.9 }]);

  assert.equal(
    win.dataLayer.filter((item) => item.event === 'newsletter_section_view').length,
    1
  );
  assert.equal(
    win.dataLayer.find((item) => item.event === 'newsletter_section_view').newsletter_section,
    'leadership_message'
  );
});

test('tracks a tall section when it enters the central viewport band', () => {
  let observerCallback;
  let observerOptions;
  const section = { dataset: { flsSection: 'special_news' } };
  const document = {
    readyState: 'complete',
    body: { scrollHeight: 5000 },
    documentElement: { scrollHeight: 5000 },
    addEventListener() {},
    querySelectorAll(selector) {
      return selector === '[data-fls-section]' ? [section] : [];
    },
  };
  const win = {
    document,
    location: {
      href: 'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/',
      pathname: '/INT-FLS-Newsletter-Q3-2026/',
    },
    dataLayer: [],
    innerHeight: 900,
    scrollY: 0,
    addEventListener() {},
    IntersectionObserver: class {
      constructor(callback, options) {
        observerCallback = callback;
        observerOptions = options;
      }
      observe() {}
      unobserve() {}
    },
  };

  tracking.init(win);

  assert.deepEqual(observerOptions, {
    rootMargin: '-20% 0px -20% 0px',
    threshold: [0],
  });

  observerCallback([{ target: section, isIntersecting: true, intersectionRatio: 0.27 }]);

  const event = win.dataLayer.find((item) => item.event === 'newsletter_section_view');
  assert.equal(event.newsletter_section, 'special_news');
});

test('delegated marked CTA click emits one CTA event rather than an outbound duplicate', () => {
  const listeners = {};
  const section = { dataset: { flsSection: 'leadership_message' } };
  const anchor = {
    tagName: 'A',
    dataset: { flsTrack: 'cta_click', flsLabel: 'contact_team' },
    getAttribute(name) {
      return name === 'href' ? 'https://fls-group.com/contact/?email=private' : null;
    },
    closest(selector) {
      return selector === '[data-fls-section]' ? section : null;
    },
  };
  const target = {
    closest(selector) {
      return selector === '[data-fls-track], a[href]' ? anchor : null;
    },
  };
  const document = {
    readyState: 'complete',
    body: { scrollHeight: 1000 },
    documentElement: { scrollHeight: 1000 },
    addEventListener(name, handler) {
      listeners[name] = handler;
    },
    querySelectorAll() {
      return [];
    },
  };
  const win = {
    document,
    location: {
      href: 'https://newsletter.fls-group.com/INT-FLS-Newsletter-Q3-2026/',
      pathname: '/INT-FLS-Newsletter-Q3-2026/',
    },
    dataLayer: [],
    innerHeight: 100,
    scrollY: 0,
    addEventListener() {},
  };

  tracking.init(win);
  listeners.click({ target });

  const clicks = win.dataLayer.filter((item) => item.event && item.event.includes('_click'));
  assert.equal(clicks.length, 1);
  assert.deepEqual(clicks[0], {
    event: 'newsletter_cta_click',
    newsletter_issue: 'Q3-2026',
    newsletter_audience: 'INT',
    newsletter_slug: 'INT-FLS-Newsletter-Q3-2026',
    newsletter_section: 'leadership_message',
    cta_label: 'contact_team',
    link_url: 'https://fls-group.com/contact/',
    link_host: 'fls-group.com',
  });
});

test('classifies a marked video play using semantic attributes', () => {
  assert.deepEqual(
    tracking.classifyMarkedInteraction(
      { flsTrack: 'video_play', flsVideo: 'ceo_interview' },
      'leadership_message'
    ),
    {
      event: 'newsletter_video_play',
      params: {
        newsletter_section: 'leadership_message',
        video_name: 'ceo_interview',
      },
    }
  );
});

test('classifies a marked carousel interaction using semantic attributes', () => {
  assert.deepEqual(
    tracking.classifyMarkedInteraction(
      {
        flsTrack: 'carousel_interaction',
        flsCarousel: 'project_stories',
        flsItem: 'cold_chain',
        flsAction: 'next',
      },
      'case_studies'
    ),
    {
      event: 'newsletter_carousel_interaction',
      params: {
        newsletter_section: 'case_studies',
        carousel_name: 'project_stories',
        carousel_item: 'cold_chain',
        interaction_action: 'next',
      },
    }
  );
});

test('delegated marked button emits its semantic video event', () => {
  const listeners = {};
  const section = { dataset: { flsSection: 'leadership_message' } };
  const button = {
    tagName: 'BUTTON',
    dataset: { flsTrack: 'video_play', flsVideo: 'ceo_interview' },
    closest(selector) {
      return selector === '[data-fls-section]' ? section : null;
    },
  };
  const target = {
    closest(selector) {
      return selector === '[data-fls-track], a[href]' ? button : null;
    },
  };
  const document = {
    readyState: 'complete',
    body: { scrollHeight: 1000 },
    documentElement: { scrollHeight: 1000 },
    addEventListener(name, handler) {
      listeners[name] = handler;
    },
    querySelectorAll() {
      return [];
    },
  };
  const win = {
    document,
    location: {
      href: 'https://newsletter.fls-group.com/EXT-FLS-Newsletter-Q3-2026/',
      pathname: '/EXT-FLS-Newsletter-Q3-2026/',
    },
    dataLayer: [],
    innerHeight: 100,
    scrollY: 0,
    addEventListener() {},
  };

  tracking.init(win);
  listeners.click({ target });

  const videoEvent = win.dataLayer.find((item) => item.event === 'newsletter_video_play');
  assert.equal(videoEvent.newsletter_section, 'leadership_message');
  assert.equal(videoEvent.video_name, 'ceo_interview');
});
