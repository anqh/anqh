/* ===================================================
 * bootstrap-transition.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#transitions
 * ===================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


  /* CSS TRANSITION SUPPORT (http://www.modernizr.com/)
   * ======================================================= */

  $(function () {

    $.support.transition = (function () {

      var transitionEnd = (function () {

        var el = document.createElement('bootstrap')
          , transEndEventNames = {
               'WebkitTransition' : 'webkitTransitionEnd'
            ,  'MozTransition'    : 'transitionend'
            ,  'OTransition'      : 'oTransitionEnd otransitionend'
            ,  'transition'       : 'transitionend'
            }
          , name

        for (name in transEndEventNames){
          if (el.style[name] !== undefined) {
            return transEndEventNames[name]
          }
        }

      }())

      return transitionEnd && {
        end: transitionEnd
      }

    })()

  })

}(window.jQuery);/* ==========================================================
 * bootstrap-alert.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#alerts
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* ALERT CLASS DEFINITION
  * ====================== */

  var dismiss = '[data-dismiss="alert"]'
    , Alert = function (el) {
        $(el).on('click', dismiss, this.close)
      }

  Alert.prototype.close = function (e) {
    var $this = $(this)
      , selector = $this.attr('data-target')
      , $parent

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = $(selector)

    e && e.preventDefault()

    $parent.length || ($parent = $this.hasClass('alert') ? $this : $this.parent())

    $parent.trigger(e = $.Event('close'))

    if (e.isDefaultPrevented()) return

    $parent.removeClass('in')

    function removeElement() {
      $parent
        .trigger('closed')
        .remove()
    }

    $.support.transition && $parent.hasClass('fade') ?
      $parent.on($.support.transition.end, removeElement) :
      removeElement()
  }


 /* ALERT PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.alert

  $.fn.alert = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('alert')
      if (!data) $this.data('alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  $.fn.alert.Constructor = Alert


 /* ALERT NO CONFLICT
  * ================= */

  $.fn.alert.noConflict = function () {
    $.fn.alert = old
    return this
  }


 /* ALERT DATA-API
  * ============== */

  $(document).on('click.alert.data-api', dismiss, Alert.prototype.close)

}(window.jQuery);/* ============================================================
 * bootstrap-button.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* BUTTON PUBLIC CLASS DEFINITION
  * ============================== */

  var Button = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.button.defaults, options)
  }

  Button.prototype.setState = function (state) {
    var d = 'disabled'
      , $el = this.$element
      , data = $el.data()
      , val = $el.is('input') ? 'val' : 'html'

    state = state + 'Text'
    data.resetText || $el.data('resetText', $el[val]())

    $el[val](data[state] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).attr(d, d) :
        $el.removeClass(d).removeAttr(d)
    }, 0)
  }

  Button.prototype.toggle = function () {
    var $parent = this.$element.closest('[data-toggle="buttons-radio"]')

    $parent && $parent
      .find('.active')
      .removeClass('active')

    this.$element.toggleClass('active')
  }


 /* BUTTON PLUGIN DEFINITION
  * ======================== */

  var old = $.fn.button

  $.fn.button = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('button')
        , options = typeof option == 'object' && option
      if (!data) $this.data('button', (data = new Button(this, options)))
      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }

  $.fn.button.defaults = {
    loadingText: 'loading...'
  }

  $.fn.button.Constructor = Button


 /* BUTTON NO CONFLICT
  * ================== */

  $.fn.button.noConflict = function () {
    $.fn.button = old
    return this
  }


 /* BUTTON DATA-API
  * =============== */

  $(document).on('click.button.data-api', '[data-toggle^=button]', function (e) {
    var $btn = $(e.target)
    if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
    $btn.button('toggle')
  })

}(window.jQuery);/* ==========================================================
 * bootstrap-carousel.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* CAROUSEL CLASS DEFINITION
  * ========================= */

  var Carousel = function (element, options) {
    this.$element = $(element)
    this.$indicators = this.$element.find('.carousel-indicators')
    this.options = options
    this.options.pause == 'hover' && this.$element
      .on('mouseenter', $.proxy(this.pause, this))
      .on('mouseleave', $.proxy(this.cycle, this))
  }

  Carousel.prototype = {

    cycle: function (e) {
      if (!e) this.paused = false
      if (this.interval) clearInterval(this.interval);
      this.options.interval
        && !this.paused
        && (this.interval = setInterval($.proxy(this.next, this), this.options.interval))
      return this
    }

  , getActiveIndex: function () {
      this.$active = this.$element.find('.item.active')
      this.$items = this.$active.parent().children()
      return this.$items.index(this.$active)
    }

  , to: function (pos) {
      var activeIndex = this.getActiveIndex()
        , that = this

      if (pos > (this.$items.length - 1) || pos < 0) return

      if (this.sliding) {
        return this.$element.one('slid', function () {
          that.to(pos)
        })
      }

      if (activeIndex == pos) {
        return this.pause().cycle()
      }

      return this.slide(pos > activeIndex ? 'next' : 'prev', $(this.$items[pos]))
    }

  , pause: function (e) {
      if (!e) this.paused = true
      if (this.$element.find('.next, .prev').length && $.support.transition.end) {
        this.$element.trigger($.support.transition.end)
        this.cycle()
      }
      clearInterval(this.interval)
      this.interval = null
      return this
    }

  , next: function () {
      if (this.sliding) return
      return this.slide('next')
    }

  , prev: function () {
      if (this.sliding) return
      return this.slide('prev')
    }

  , slide: function (type, next) {
      var $active = this.$element.find('.item.active')
        , $next = next || $active[type]()
        , isCycling = this.interval
        , direction = type == 'next' ? 'left' : 'right'
        , fallback  = type == 'next' ? 'first' : 'last'
        , that = this
        , e

      this.sliding = true

      isCycling && this.pause()

      $next = $next.length ? $next : this.$element.find('.item')[fallback]()

      e = $.Event('slide', {
        relatedTarget: $next[0]
      , direction: direction
      })

      if ($next.hasClass('active')) return

      if (this.$indicators.length) {
        this.$indicators.find('.active').removeClass('active')
        this.$element.one('slid', function () {
          var $nextIndicator = $(that.$indicators.children()[that.getActiveIndex()])
          $nextIndicator && $nextIndicator.addClass('active')
        })
      }

      if ($.support.transition && this.$element.hasClass('slide')) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $next.addClass(type)
        $next[0].offsetWidth // force reflow
        $active.addClass(direction)
        $next.addClass(direction)
        this.$element.one($.support.transition.end, function () {
          $next.removeClass([type, direction].join(' ')).addClass('active')
          $active.removeClass(['active', direction].join(' '))
          that.sliding = false
          setTimeout(function () { that.$element.trigger('slid') }, 0)
        })
      } else {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $active.removeClass('active')
        $next.addClass('active')
        this.sliding = false
        this.$element.trigger('slid')
      }

      isCycling && this.cycle()

      return this
    }

  }


 /* CAROUSEL PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.carousel

  $.fn.carousel = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('carousel')
        , options = $.extend({}, $.fn.carousel.defaults, typeof option == 'object' && option)
        , action = typeof option == 'string' ? option : options.slide
      if (!data) $this.data('carousel', (data = new Carousel(this, options)))
      if (typeof option == 'number') data.to(option)
      else if (action) data[action]()
      else if (options.interval) data.pause().cycle()
    })
  }

  $.fn.carousel.defaults = {
    interval: 5000
  , pause: 'hover'
  }

  $.fn.carousel.Constructor = Carousel


 /* CAROUSEL NO CONFLICT
  * ==================== */

  $.fn.carousel.noConflict = function () {
    $.fn.carousel = old
    return this
  }

 /* CAROUSEL DATA-API
  * ================= */

  $(document).on('click.carousel.data-api', '[data-slide], [data-slide-to]', function (e) {
    var $this = $(this), href
      , $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      , options = $.extend({}, $target.data(), $this.data())
      , slideIndex

    $target.carousel(options)

    if (slideIndex = $this.attr('data-slide-to')) {
      $target.data('carousel').pause().to(slideIndex).cycle()
    }

    e.preventDefault()
  })

}(window.jQuery);/* =============================================================
 * bootstrap-collapse.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#collapse
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* COLLAPSE PUBLIC CLASS DEFINITION
  * ================================ */

  var Collapse = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.collapse.defaults, options)

    if (this.options.parent) {
      this.$parent = $(this.options.parent)
    }

    this.options.toggle && this.toggle()
  }

  Collapse.prototype = {

    constructor: Collapse

  , dimension: function () {
      var hasWidth = this.$element.hasClass('width')
      return hasWidth ? 'width' : 'height'
    }

  , show: function () {
      var dimension
        , scroll
        , actives
        , hasData

      if (this.transitioning || this.$element.hasClass('in')) return

      dimension = this.dimension()
      scroll = $.camelCase(['scroll', dimension].join('-'))
      actives = this.$parent && this.$parent.find('> .accordion-group > .in')

      if (actives && actives.length) {
        hasData = actives.data('collapse')
        if (hasData && hasData.transitioning) return
        actives.collapse('hide')
        hasData || actives.data('collapse', null)
      }

      this.$element[dimension](0)
      this.transition('addClass', $.Event('show'), 'shown')
      $.support.transition && this.$element[dimension](this.$element[0][scroll])
    }

  , hide: function () {
      var dimension
      if (this.transitioning || !this.$element.hasClass('in')) return
      dimension = this.dimension()
      this.reset(this.$element[dimension]())
      this.transition('removeClass', $.Event('hide'), 'hidden')
      this.$element[dimension](0)
    }

  , reset: function (size) {
      var dimension = this.dimension()

      this.$element
        .removeClass('collapse')
        [dimension](size || 'auto')
        [0].offsetWidth

      this.$element[size !== null ? 'addClass' : 'removeClass']('collapse')

      return this
    }

  , transition: function (method, startEvent, completeEvent) {
      var that = this
        , complete = function () {
            if (startEvent.type == 'show') that.reset()
            that.transitioning = 0
            that.$element.trigger(completeEvent)
          }

      this.$element.trigger(startEvent)

      if (startEvent.isDefaultPrevented()) return

      this.transitioning = 1

      this.$element[method]('in')

      $.support.transition && this.$element.hasClass('collapse') ?
        this.$element.one($.support.transition.end, complete) :
        complete()
    }

  , toggle: function () {
      this[this.$element.hasClass('in') ? 'hide' : 'show']()
    }

  }


 /* COLLAPSE PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.collapse

  $.fn.collapse = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('collapse')
        , options = $.extend({}, $.fn.collapse.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.collapse.defaults = {
    toggle: true
  }

  $.fn.collapse.Constructor = Collapse


 /* COLLAPSE NO CONFLICT
  * ==================== */

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


 /* COLLAPSE DATA-API
  * ================= */

  $(document).on('click.collapse.data-api', '[data-toggle=collapse]', function (e) {
    var $this = $(this), href
      , target = $this.attr('data-target')
        || e.preventDefault()
        || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
      , option = $(target).data('collapse') ? 'toggle' : $this.data()
    $this[$(target).hasClass('in') ? 'addClass' : 'removeClass']('collapsed')
    $(target).collapse(option)
  })

}(window.jQuery);/* ============================================================
 * bootstrap-dropdown.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#dropdowns
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* DROPDOWN CLASS DEFINITION
  * ========================= */

  var toggle = '[data-toggle=dropdown]'
    , Dropdown = function (element) {
        var $el = $(element).on('click.dropdown.data-api', this.toggle)
        $('html').on('click.dropdown.data-api', function () {
          $el.parent().removeClass('open')
        })
      }

  Dropdown.prototype = {

    constructor: Dropdown

  , toggle: function (e) {
      var $this = $(this)
        , $parent
        , isActive

      if ($this.is('.disabled, :disabled')) return

      $parent = getParent($this)

      isActive = $parent.hasClass('open')

      clearMenus()

      if (!isActive) {
        $parent.toggleClass('open')
      }

      $this.focus()

      return false
    }

  , keydown: function (e) {
      var $this
        , $items
        , $active
        , $parent
        , isActive
        , index

      if (!/(38|40|27)/.test(e.keyCode)) return

      $this = $(this)

      e.preventDefault()
      e.stopPropagation()

      if ($this.is('.disabled, :disabled')) return

      $parent = getParent($this)

      isActive = $parent.hasClass('open')

      if (!isActive || (isActive && e.keyCode == 27)) {
        if (e.which == 27) $parent.find(toggle).focus()
        return $this.click()
      }

      $items = $('[role=menu] li:not(.divider):visible a', $parent)

      if (!$items.length) return

      index = $items.index($items.filter(':focus'))

      if (e.keyCode == 38 && index > 0) index--                                        // up
      if (e.keyCode == 40 && index < $items.length - 1) index++                        // down
      if (!~index) index = 0

      $items
        .eq(index)
        .focus()
    }

  }

  function clearMenus() {
    $(toggle).each(function () {
      getParent($(this)).removeClass('open')
    })
  }

  function getParent($this) {
    var selector = $this.attr('data-target')
      , $parent

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = selector && $(selector)

    if (!$parent || !$parent.length) $parent = $this.parent()

    return $parent
  }


  /* DROPDOWN PLUGIN DEFINITION
   * ========================== */

  var old = $.fn.dropdown

  $.fn.dropdown = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('dropdown')
      if (!data) $this.data('dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  $.fn.dropdown.Constructor = Dropdown


 /* DROPDOWN NO CONFLICT
  * ==================== */

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old
    return this
  }


  /* APPLY TO STANDARD DROPDOWN ELEMENTS
   * =================================== */

  $(document)
    .on('click.dropdown.data-api', clearMenus)
    .on('click.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
    .on('.dropdown-menu', function (e) { e.stopPropagation() })
    .on('click.dropdown.data-api'  , toggle, Dropdown.prototype.toggle)
    .on('keydown.dropdown.data-api', toggle + ', [role=menu]' , Dropdown.prototype.keydown)

}(window.jQuery);
/* =========================================================
 * bootstrap-modal.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#modals
 * =========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================= */


!function ($) {

  "use strict"; // jshint ;_;


 /* MODAL CLASS DEFINITION
  * ====================== */

  var Modal = function (element, options) {
    this.options = options
    this.$element = $(element)
      .delegate('[data-dismiss="modal"]', 'click.dismiss.modal', $.proxy(this.hide, this))
    this.options.remote && this.$element.find('.modal-body').load(this.options.remote)
  }

  Modal.prototype = {

      constructor: Modal

    , toggle: function () {
        return this[!this.isShown ? 'show' : 'hide']()
      }

    , show: function () {
        var that = this
          , e = $.Event('show')

        this.$element.trigger(e)

        if (this.isShown || e.isDefaultPrevented()) return

        this.isShown = true

        this.escape()

        this.backdrop(function () {
          var transition = $.support.transition && that.$element.hasClass('fade')

          if (!that.$element.parent().length) {
            that.$element.appendTo(document.body) //don't move modals dom position
          }

          that.$element.show()

          if (transition) {
            that.$element[0].offsetWidth // force reflow
          }

          that.$element
            .addClass('in')
            .attr('aria-hidden', false)

          that.enforceFocus()

          transition ?
            that.$element.one($.support.transition.end, function () { that.$element.focus().trigger('shown') }) :
            that.$element.focus().trigger('shown')

        })
      }

    , hide: function (e) {
        e && e.preventDefault()

        var that = this

        e = $.Event('hide')

        this.$element.trigger(e)

        if (!this.isShown || e.isDefaultPrevented()) return

        this.isShown = false

        this.escape()

        $(document).off('focusin.modal')

        this.$element
          .removeClass('in')
          .attr('aria-hidden', true)

        $.support.transition && this.$element.hasClass('fade') ?
          this.hideWithTransition() :
          this.hideModal()
      }

    , enforceFocus: function () {
        var that = this
        $(document).on('focusin.modal', function (e) {
          if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
            that.$element.focus()
          }
        })
      }

    , escape: function () {
        var that = this
        if (this.isShown && this.options.keyboard) {
          this.$element.on('keyup.dismiss.modal', function ( e ) {
            e.which == 27 && that.hide()
          })
        } else if (!this.isShown) {
          this.$element.off('keyup.dismiss.modal')
        }
      }

    , hideWithTransition: function () {
        var that = this
          , timeout = setTimeout(function () {
              that.$element.off($.support.transition.end)
              that.hideModal()
            }, 500)

        this.$element.one($.support.transition.end, function () {
          clearTimeout(timeout)
          that.hideModal()
        })
      }

    , hideModal: function () {
        var that = this
        this.$element.hide()
        this.backdrop(function () {
          that.removeBackdrop()
          that.$element.trigger('hidden')
        })
      }

    , removeBackdrop: function () {
        this.$backdrop.remove()
        this.$backdrop = null
      }

    , backdrop: function (callback) {
        var that = this
          , animate = this.$element.hasClass('fade') ? 'fade' : ''

        if (this.isShown && this.options.backdrop) {
          var doAnimate = $.support.transition && animate

          this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
            .appendTo(document.body)

          this.$backdrop.click(
            this.options.backdrop == 'static' ?
              $.proxy(this.$element[0].focus, this.$element[0])
            : $.proxy(this.hide, this)
          )

          if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

          this.$backdrop.addClass('in')

          if (!callback) return

          doAnimate ?
            this.$backdrop.one($.support.transition.end, callback) :
            callback()

        } else if (!this.isShown && this.$backdrop) {
          this.$backdrop.removeClass('in')

          $.support.transition && this.$element.hasClass('fade')?
            this.$backdrop.one($.support.transition.end, callback) :
            callback()

        } else if (callback) {
          callback()
        }
      }
  }


 /* MODAL PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.modal

  $.fn.modal = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('modal')
        , options = $.extend({}, $.fn.modal.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('modal', (data = new Modal(this, options)))
      if (typeof option == 'string') data[option]()
      else if (options.show) data.show()
    })
  }

  $.fn.modal.defaults = {
      backdrop: true
    , keyboard: true
    , show: true
  }

  $.fn.modal.Constructor = Modal


 /* MODAL NO CONFLICT
  * ================= */

  $.fn.modal.noConflict = function () {
    $.fn.modal = old
    return this
  }


 /* MODAL DATA-API
  * ============== */

  $(document).on('click.modal.data-api', '[data-toggle="modal"]', function (e) {
    var $this = $(this)
      , href = $this.attr('href')
      , $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
      , option = $target.data('modal') ? 'toggle' : $.extend({ remote:!/#/.test(href) && href }, $target.data(), $this.data())

    e.preventDefault()

    $target
      .modal(option)
      .one('hide', function () {
        $this.focus()
      })
  })

}(window.jQuery);
/* ===========================================================
 * bootstrap-tooltip.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#tooltips
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* TOOLTIP PUBLIC CLASS DEFINITION
  * =============================== */

  var Tooltip = function (element, options) {
    this.init('tooltip', element, options)
  }

  Tooltip.prototype = {

    constructor: Tooltip

  , init: function (type, element, options) {
      var eventIn
        , eventOut
        , triggers
        , trigger
        , i

      this.type = type
      this.$element = $(element)
      this.options = this.getOptions(options)
      this.enabled = true

      triggers = this.options.trigger.split(' ')

      for (i = triggers.length; i--;) {
        trigger = triggers[i]
        if (trigger == 'click') {
          this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this))
        } else if (trigger != 'manual') {
          eventIn = trigger == 'hover' ? 'mouseenter' : 'focus'
          eventOut = trigger == 'hover' ? 'mouseleave' : 'blur'
          this.$element.on(eventIn + '.' + this.type, this.options.selector, $.proxy(this.enter, this))
          this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this))
        }
      }

      this.options.selector ?
        (this._options = $.extend({}, this.options, { trigger: 'manual', selector: '' })) :
        this.fixTitle()
    }

  , getOptions: function (options) {
      options = $.extend({}, $.fn[this.type].defaults, this.$element.data(), options)

      if (options.delay && typeof options.delay == 'number') {
        options.delay = {
          show: options.delay
        , hide: options.delay
        }
      }

      return options
    }

  , enter: function (e) {
      var self = $(e.currentTarget)[this.type](this._options).data(this.type)

      if (!self.options.delay || !self.options.delay.show) return self.show()

      clearTimeout(this.timeout)
      self.hoverState = 'in'
      this.timeout = setTimeout(function() {
        if (self.hoverState == 'in') self.show()
      }, self.options.delay.show)
    }

  , leave: function (e) {
      var self = $(e.currentTarget)[this.type](this._options).data(this.type)

      if (this.timeout) clearTimeout(this.timeout)
      if (!self.options.delay || !self.options.delay.hide) return self.hide()

      self.hoverState = 'out'
      this.timeout = setTimeout(function() {
        if (self.hoverState == 'out') self.hide()
      }, self.options.delay.hide)
    }

  , show: function () {
      var $tip
        , pos
        , actualWidth
        , actualHeight
        , placement
        , tp
        , e = $.Event('show')

      if (this.hasContent() && this.enabled) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $tip = this.tip()
        this.setContent()

        if (this.options.animation) {
          $tip.addClass('fade')
        }

        placement = typeof this.options.placement == 'function' ?
          this.options.placement.call(this, $tip[0], this.$element[0]) :
          this.options.placement

        $tip
          .detach()
          .css({ top: 0, left: 0, display: 'block' })

        this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)

        pos = this.getPosition()

        actualWidth = $tip[0].offsetWidth
        actualHeight = $tip[0].offsetHeight

        switch (placement) {
          case 'bottom':
            tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'top':
            tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'left':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth}
            break
          case 'right':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width}
            break
        }

        this.applyPlacement(tp, placement)
        this.$element.trigger('shown')
      }
    }

  , applyPlacement: function(offset, placement){
      var $tip = this.tip()
        , width = $tip[0].offsetWidth
        , height = $tip[0].offsetHeight
        , actualWidth
        , actualHeight
        , delta
        , replace

      $tip
        .offset(offset)
        .addClass(placement)
        .addClass('in')

      actualWidth = $tip[0].offsetWidth
      actualHeight = $tip[0].offsetHeight

      if (placement == 'top' && actualHeight != height) {
        offset.top = offset.top + height - actualHeight
        replace = true
      }

      if (placement == 'bottom' || placement == 'top') {
        delta = 0

        if (offset.left < 0){
          delta = offset.left * -2
          offset.left = 0
          $tip.offset(offset)
          actualWidth = $tip[0].offsetWidth
          actualHeight = $tip[0].offsetHeight
        }

        this.replaceArrow(delta - width + actualWidth, actualWidth, 'left')
      } else {
        this.replaceArrow(actualHeight - height, actualHeight, 'top')
      }

      if (replace) $tip.offset(offset)
    }

  , replaceArrow: function(delta, dimension, position){
      this
        .arrow()
        .css(position, delta ? (50 * (1 - delta / dimension) + "%") : '')
    }

  , setContent: function () {
      var $tip = this.tip()
        , title = this.getTitle()

      $tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title)
      $tip.removeClass('fade in top bottom left right')
    }

  , hide: function () {
      var that = this
        , $tip = this.tip()
        , e = $.Event('hide')

      this.$element.trigger(e)
      if (e.isDefaultPrevented()) return

      $tip.removeClass('in')

      function removeWithAnimation() {
        var timeout = setTimeout(function () {
          $tip.off($.support.transition.end).detach()
        }, 500)

        $tip.one($.support.transition.end, function () {
          clearTimeout(timeout)
          $tip.detach()
        })
      }

      $.support.transition && this.$tip.hasClass('fade') ?
        removeWithAnimation() :
        $tip.detach()

      this.$element.trigger('hidden')

      return this
    }

  , fixTitle: function () {
      var $e = this.$element
      if ($e.attr('title') || typeof($e.attr('data-original-title')) != 'string') {
        $e.attr('data-original-title', $e.attr('title') || '').attr('title', '')
      }
    }

  , hasContent: function () {
      return this.getTitle()
    }

  , getPosition: function () {
      var el = this.$element[0]
      return $.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : {
        width: el.offsetWidth
      , height: el.offsetHeight
      }, this.$element.offset())
    }

  , getTitle: function () {
      var title
        , $e = this.$element
        , o = this.options

      title = $e.attr('data-original-title')
        || (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title)

      return title
    }

  , tip: function () {
      return this.$tip = this.$tip || $(this.options.template)
    }

  , arrow: function(){
      return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow")
    }

  , validate: function () {
      if (!this.$element[0].parentNode) {
        this.hide()
        this.$element = null
        this.options = null
      }
    }

  , enable: function () {
      this.enabled = true
    }

  , disable: function () {
      this.enabled = false
    }

  , toggleEnabled: function () {
      this.enabled = !this.enabled
    }

  , toggle: function (e) {
      var self = e ? $(e.currentTarget)[this.type](this._options).data(this.type) : this
      self.tip().hasClass('in') ? self.hide() : self.show()
    }

  , destroy: function () {
      this.hide().$element.off('.' + this.type).removeData(this.type)
    }

  }


 /* TOOLTIP PLUGIN DEFINITION
  * ========================= */

  var old = $.fn.tooltip

  $.fn.tooltip = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tooltip')
        , options = typeof option == 'object' && option
      if (!data) $this.data('tooltip', (data = new Tooltip(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.tooltip.Constructor = Tooltip

  $.fn.tooltip.defaults = {
    animation: true
  , placement: 'top'
  , selector: false
  , template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
  , trigger: 'hover focus'
  , title: ''
  , delay: 0
  , html: false
  , container: false
  }


 /* TOOLTIP NO CONFLICT
  * =================== */

  $.fn.tooltip.noConflict = function () {
    $.fn.tooltip = old
    return this
  }

}(window.jQuery);
/* ===========================================================
 * bootstrap-popover.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#popovers
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* POPOVER PUBLIC CLASS DEFINITION
  * =============================== */

  var Popover = function (element, options) {
    this.init('popover', element, options)
  }


  /* NOTE: POPOVER EXTENDS BOOTSTRAP-TOOLTIP.js
     ========================================== */

  Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype, {

    constructor: Popover

  , setContent: function () {
      var $tip = this.tip()
        , title = this.getTitle()
        , content = this.getContent()

      $tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
      $tip.find('.popover-content')[this.options.html ? 'html' : 'text'](content)

      $tip.removeClass('fade top bottom left right in')
    }

  , hasContent: function () {
      return this.getTitle() || this.getContent()
    }

  , getContent: function () {
      var content
        , $e = this.$element
        , o = this.options

      content = (typeof o.content == 'function' ? o.content.call($e[0]) :  o.content)
        || $e.attr('data-content')

      return content
    }

  , tip: function () {
      if (!this.$tip) {
        this.$tip = $(this.options.template)
      }
      return this.$tip
    }

  , destroy: function () {
      this.hide().$element.off('.' + this.type).removeData(this.type)
    }

  })


 /* POPOVER PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.popover

  $.fn.popover = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('popover')
        , options = typeof option == 'object' && option
      if (!data) $this.data('popover', (data = new Popover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.popover.Constructor = Popover

  $.fn.popover.defaults = $.extend({} , $.fn.tooltip.defaults, {
    placement: 'right'
  , trigger: 'click'
  , content: ''
  , template: '<div class="popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
  })


 /* POPOVER NO CONFLICT
  * =================== */

  $.fn.popover.noConflict = function () {
    $.fn.popover = old
    return this
  }

}(window.jQuery);
/* =============================================================
 * bootstrap-scrollspy.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#scrollspy
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* SCROLLSPY CLASS DEFINITION
  * ========================== */

  function ScrollSpy(element, options) {
    var process = $.proxy(this.process, this)
      , $element = $(element).is('body') ? $(window) : $(element)
      , href
    this.options = $.extend({}, $.fn.scrollspy.defaults, options)
    this.$scrollElement = $element.on('scroll.scroll-spy.data-api', process)
    this.selector = (this.options.target
      || ((href = $(element).attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      || '') + ' .nav li > a'
    this.$body = $('body')
    this.refresh()
    this.process()
  }

  ScrollSpy.prototype = {

      constructor: ScrollSpy

    , refresh: function () {
        var self = this
          , $targets

        this.offsets = $([])
        this.targets = $([])

        $targets = this.$body
          .find(this.selector)
          .map(function () {
            var $el = $(this)
              , href = $el.data('target') || $el.attr('href')
              , $href = /^#\w/.test(href) && $(href)
            return ( $href
              && $href.length
              && [[ $href.position().top + (!$.isWindow(self.$scrollElement.get(0)) && self.$scrollElement.scrollTop()), href ]] ) || null
          })
          .sort(function (a, b) { return a[0] - b[0] })
          .each(function () {
            self.offsets.push(this[0])
            self.targets.push(this[1])
          })
      }

    , process: function () {
        var scrollTop = this.$scrollElement.scrollTop() + this.options.offset
          , scrollHeight = this.$scrollElement[0].scrollHeight || this.$body[0].scrollHeight
          , maxScroll = scrollHeight - this.$scrollElement.height()
          , offsets = this.offsets
          , targets = this.targets
          , activeTarget = this.activeTarget
          , i

        if (scrollTop >= maxScroll) {
          return activeTarget != (i = targets.last()[0])
            && this.activate ( i )
        }

        for (i = offsets.length; i--;) {
          activeTarget != targets[i]
            && scrollTop >= offsets[i]
            && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
            && this.activate( targets[i] )
        }
      }

    , activate: function (target) {
        var active
          , selector

        this.activeTarget = target

        $(this.selector)
          .parent('.active')
          .removeClass('active')

        selector = this.selector
          + '[data-target="' + target + '"],'
          + this.selector + '[href="' + target + '"]'

        active = $(selector)
          .parent('li')
          .addClass('active')

        if (active.parent('.dropdown-menu').length)  {
          active = active.closest('li.dropdown').addClass('active')
        }

        active.trigger('activate')
      }

  }


 /* SCROLLSPY PLUGIN DEFINITION
  * =========================== */

  var old = $.fn.scrollspy

  $.fn.scrollspy = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('scrollspy')
        , options = typeof option == 'object' && option
      if (!data) $this.data('scrollspy', (data = new ScrollSpy(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.scrollspy.Constructor = ScrollSpy

  $.fn.scrollspy.defaults = {
    offset: 10
  }


 /* SCROLLSPY NO CONFLICT
  * ===================== */

  $.fn.scrollspy.noConflict = function () {
    $.fn.scrollspy = old
    return this
  }


 /* SCROLLSPY DATA-API
  * ================== */

  $(window).on('load', function () {
    $('[data-spy="scroll"]').each(function () {
      var $spy = $(this)
      $spy.scrollspy($spy.data())
    })
  })

}(window.jQuery);/* ========================================================
 * bootstrap-tab.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* TAB CLASS DEFINITION
  * ==================== */

  var Tab = function (element) {
    this.element = $(element)
  }

  Tab.prototype = {

    constructor: Tab

  , show: function () {
      var $this = this.element
        , $ul = $this.closest('ul:not(.dropdown-menu)')
        , selector = $this.attr('data-target')
        , previous
        , $target
        , e

      if (!selector) {
        selector = $this.attr('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
      }

      if ( $this.parent('li').hasClass('active') ) return

      previous = $ul.find('.active:last a')[0]

      e = $.Event('show', {
        relatedTarget: previous
      })

      $this.trigger(e)

      if (e.isDefaultPrevented()) return

      $target = $(selector)

      this.activate($this.parent('li'), $ul)
      this.activate($target, $target.parent(), function () {
        $this.trigger({
          type: 'shown'
        , relatedTarget: previous
        })
      })
    }

  , activate: function ( element, container, callback) {
      var $active = container.find('> .active')
        , transition = callback
            && $.support.transition
            && $active.hasClass('fade')

      function next() {
        $active
          .removeClass('active')
          .find('> .dropdown-menu > .active')
          .removeClass('active')

        element.addClass('active')

        if (transition) {
          element[0].offsetWidth // reflow for transition
          element.addClass('in')
        } else {
          element.removeClass('fade')
        }

        if ( element.parent('.dropdown-menu') ) {
          element.closest('li.dropdown').addClass('active')
        }

        callback && callback()
      }

      transition ?
        $active.one($.support.transition.end, next) :
        next()

      $active.removeClass('in')
    }
  }


 /* TAB PLUGIN DEFINITION
  * ===================== */

  var old = $.fn.tab

  $.fn.tab = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tab')
      if (!data) $this.data('tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.tab.Constructor = Tab


 /* TAB NO CONFLICT
  * =============== */

  $.fn.tab.noConflict = function () {
    $.fn.tab = old
    return this
  }


 /* TAB DATA-API
  * ============ */

  $(document).on('click.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
    e.preventDefault()
    $(this).tab('show')
  })

}(window.jQuery);/* =============================================================
 * bootstrap-typeahead.js v2.3.0-j4
 * http://twitter.github.com/bootstrap/javascript.html#typeahead
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function($){

  "use strict"; // jshint ;_;


 /* TYPEAHEAD PUBLIC CLASS DEFINITION
  * ================================= */

  var Typeahead = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.typeahead.defaults, options)
    if (this.options.target) this.$target = $(this.options.target)
    this.matcher = this.options.matcher || this.matcher
    this.sorter = this.options.sorter || this.sorter
    this.highlighter = this.options.highlighter || this.highlighter
    this.updater = this.options.updater || this.updater
    this.source = this.options.source
    this.strict = this.options.strict
    this.$menu = $(this.options.menu)
    this.shown = false

    if (typeof this.source == 'string') {
        this.url = this.source
        this.source = this.searchAjax
    }
    
    if (element.nodeName == 'SELECT') this.replaceSelect()

    this.text = this.$element.val()
    
    this.$element
      .attr('data-text', this.value)
      .attr('autocomplete', "off")
      
    if (typeof this.$target != 'undefined') this.$element.attr('data-value', this.$target.val())
      else if (typeof this.$element.attr('data-value') == 'undefined') this.$element.attr('data-value', this.strict ? '' : this.value)
    
    this.$menu.css('min-width', this.$element.width() + 12)

    this.listen()
  }

  Typeahead.prototype = {

    constructor: Typeahead

  , replaceSelect: function () {
      this.$target = this.$element
      this.$element = $('<input type="text" />')
      
      this.source = {}
      this.strict = true
      
      var options = this.$target.find('option')
      var $option;
      for (var i=0; i<options.length; i++) {
        $option = $(options[i]);
        if ($option.val() === '') {
          this.$element.attr('placeholder', $option.html());
          continue;
        }
        
        this.source[$option.val()] = $option.html()
        if (this.$target.val() == $option.val()) this.$element.val($option.html())
      }
      
      var attr = this.$target[0].attributes
      for (i=0; i<attr.length; i++) {
        if (attr[i].nodeName != 'type' && attr[i].nodeName != 'name' && attr[i].nodeName != 'id' && attr[i].nodeName != 'data-provide' && !attr[i].nodeName.match(/^on/)) {
          this.$element.attr(attr[i].nodeName, attr[i].nodeValue)
        }
      }

      this.$element.insertAfter(this.$target)
      if (this.$target.attr('autofocus')) this.$element.trigger('focus').select()
      this.$target.attr('autofocus', false)
      this.$target.hide()
    }
  
  , destroyReplacement: function () {
      // Detroy replacement element, so it doesn't mess up the browsers autofill on refresh
      if (typeof this.$target != 'undefined' && this.$target[0].nodeName == 'SELECT') {
        this.$element.replaceWith('');
      }
    }
  
  , select: function () {
      var li = this.$menu.find('.active')
        , val = li.attr('data-value')
        , text = li.find('.item-text').length > 0 ? li.find('.item-text').text() : li.text()

      val = this.updater(val, 'value')
      text = this.updater(text, 'text')

      this.$element
        .val(text)
        .attr('data-value', val)
      
      this.text = text
      
      if (typeof this.$target != 'undefined') {
        this.$target
          .val(val)
          .trigger('change')
      }
      
      this.$element.trigger('change')
      
      return this.hide()
    }

  , updater: function (text, type) {
      return text
    }

  , show: function () {
      var pos = $.extend({}, this.$element.position(), {
        height: this.$element[0].offsetHeight
      })

      this.$menu
        .insertAfter(this.$element)
        .css({
          top: pos.top + pos.height
        , left: pos.left
        })
        .show()

      this.shown = true
      return this
    }

  , hide: function () {
      this.$menu.hide()
      this.shown = false
      return this
    }

  , lookup: function (event) {
      var items

      this.query = this.$element.val()

      if (!this.query || this.query.length < this.options.minLength) {
        return this.shown ? this.hide() : this
      }

      items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source
      
      return items ? this.process(items) : this
    }

  , process: function (items) {
      return $.isArray(items) ? this.processArray(items) : this.processObject(items)
    }
    
  , processArray: function (items) {
      var that = this

      items = $.grep(items, function (item) {
        return that.matcher(item)
      })

      items = this.sorter(items)

      if (!items.length) {
        return this.shown ? this.hide() : this
      }

      return this.render(items.slice(0, this.options.items)).show()
    }

  , processObject: function (itemsIn) {
      var that = this
        , items = {}
        , i = 0

      $.each(itemsIn, function (key, item) {
        if (that.matcher(item)) items[key] = item
      })

      items = this.sorter(items)

      if ($.isEmptyObject(items)) {
        return this.shown ? this.hide() : this
      }
      
      $.each(items, function(key, item) {
        if (i++ >= that.options.items) delete items[key]
      })
      
      return this.render(items).show()
    }

  , searchAjax: function (query, process) {
      var that = this
      
      if (this.ajaxTimeout) clearTimeout(this.ajaxTimeout)

      this.ajaxTimeout = setTimeout(function () {
        if (that.ajaxTimeout) clearTimeout(that.ajaxTimeout)

        if (query === "") {
          that.hide()
          return
        }

        $.get(that.url, {'q': query, 'limit': that.options.items }, function (items) {
          if (typeof items == 'string') items = JSON.parse(items)
          process(items)
        })
      }, this.options.ajaxdelay)
  }
  
  , matcher: function (item) {
      return ~item.toLowerCase().indexOf(this.query.toLowerCase())
    }

  , sorter: function (items) {
      return $.isArray(items) ? this.sortArray(items) : this.sortObject(items)  
    }

  , sortArray: function (items) {
      var beginswith = []
        , caseSensitive = []
        , caseInsensitive = []
        , item

      while (item = items.shift()) {
        if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
        else if (~item.indexOf(this.query)) caseSensitive.push(item)
        else caseInsensitive.push(item)
      }

      return beginswith.concat(caseSensitive, caseInsensitive)
    }

  , sortObject: function (items) {
      var sorted = {}
        , key;
        
      for (key in items) {
        if (!items[key].toLowerCase().indexOf(this.query.toLowerCase())) {
          sorted[key] = items[key];
          delete items[key]
        }
      }
      
      for (key in items) {
        if (~items[key].indexOf(this.query)) {
          sorted[key] = items[key];
          delete items[key]
        }
      }

      for (key in items) {
        sorted[key] = items[key]
      }

      return sorted
    }

  , highlighter: function (item) {
      var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
      return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>'
      })
    }

  , render: function (items) {
      var that = this
        , list = $([])
      
      $.map(items, function (item, value) {
        if (list.length >= that.options.items) return
        
        var li
          , a
        
        if ($.isArray(items)) value = item
        
        li = $(that.options.item)
        a = li.find('a').length ? li.find('a') : li
        a.html(that.highlighter(item))
        
        li.attr('data-value', value)
        if (li.find('a').length === 0) li.addClass('dropdown-header')
        
        list.push(li[0])
      })

      list.not('.dropdown-header').first().addClass('active')
      
      this.$menu.html(list)
      
      return this
    }
    
  , next: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , next = active.nextAll('li:not(.dropdown-header)').first()

      if (!next.length) {
        next = $(this.$menu.find('li:not(.dropdown-header)')[0])
      }

      next.addClass('active')
    }

  , prev: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , prev = active.prevAll('li:not(.dropdown-header)').first()

      if (!prev.length) {
        prev = this.$menu.find('li:not(.dropdown-header)').last()
      }

      prev.addClass('active')
    }

  , listen: function () {
      this.$element
        .on('focus',    $.proxy(this.focus, this))
        .on('blur',     $.proxy(this.blur, this))
        .on('change',   $.proxy(this.change, this))
        .on('keypress', $.proxy(this.keypress, this))
        .on('keyup',    $.proxy(this.keyup, this))

      if (this.eventSupported('keydown')) {
        this.$element.on('keydown', $.proxy(this.keydown, this))
      }

      this.$menu
        .on('click', $.proxy(this.click, this))
        .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
        .on('mouseleave', 'li', $.proxy(this.mouseleave, this))
        
      $(window).on('unload', $.proxy(this.destroyReplacement, this))
    }

  , eventSupported: function(eventName) {
      var isSupported = eventName in this.$element
      if (!isSupported) {
        this.$element.setAttribute(eventName, 'return;')
        isSupported = typeof this.$element[eventName] === 'function'
      }
      return isSupported
    }

  , move: function (e) {
      if (!this.shown) return

      switch(e.keyCode) {
        case 9: // tab
        case 13: // enter
        case 27: // escape
          e.preventDefault()
          break

        case 38: // up arrow
          e.preventDefault()
          this.prev()
          break
          
        case 40: // down arrow
          e.preventDefault()
          this.next()
          break
      }

      e.stopPropagation()
    }

  , keydown: function (e) {
      this.suppressKeyPressRepeat = ~$.inArray(e.keyCode, [40,38,9,13,27])
      this.move(e)
    }

  , keypress: function (e) {
      if (this.suppressKeyPressRepeat) return
      this.move(e)
    }

  , keyup: function (e) {
      switch(e.keyCode) {
        case 40: // down arrow
        case 38: // up arrow
        case 16: // shift
        case 17: // ctrl
        case 18: // alt
          break

        case 9: // tab
        case 13: // enter
          if (!this.shown) return
          this.select()
          break

        case 27: // escape
          if (!this.shown) return
          this.hide()
          break

        default:
          this.lookup()
      }

      e.stopPropagation()
      e.preventDefault()
  }

  , change: function (e) {
      var value
      
      if (this.$element.val() != this.text) {
        value = this.$element.val() === '' || this.strict ? '' : this.$element.val()
            
        this.$element.val(value)
        this.$element.attr('data-value', value)
        this.text = value
        if (typeof this.$target != 'undefined') this.$target.val(value)
      }
    }

  , focus: function (e) {
      this.focused = true
    }
    
  , blur: function (e) {
      this.focused = false
      if (!this.mousedover && this.shown) this.hide()
    }

  , click: function (e) {
      e.stopPropagation()
      e.preventDefault()
      this.select()
      this.$element.focus()
    }

  , mouseenter: function (e) {
      this.mousedover = true
      this.$menu.find('.active').removeClass('active')
      $(e.currentTarget).addClass('active')
    }

  , mouseleave: function (e) {
      this.mousedover = false
      if (!this.focused && this.shown) this.hide()
    }

  }


  /* TYPEAHEAD PLUGIN DEFINITION
   * =========================== */

  var old = $.fn.typeahead

  $.fn.typeahead = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('typeahead')
        , options = typeof option == 'object' && option
      if (!data) $this.data('typeahead', (data = new Typeahead(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.typeahead.defaults = {
    source: []
  , items: 8
  , menu: '<ul class="typeahead dropdown-menu"></ul>'
  , item: '<li><a href="#"></a></li>'
  , ajaxdelay: 400
  , minLength: 1
  }

  $.fn.typeahead.Constructor = Typeahead


 /* TYPEAHEAD NO CONFLICT
  * =================== */

  $.fn.typeahead.noConflict = function () {
    $.fn.typeahead = old
    return this
  }


 /* TYPEAHEAD DATA-API
  * ================== */

  $(document)
    .off('focus.typeahead.data-api')  // overwriting Twitter's typeahead 
    .on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
      var $this = $(this)
      if ($this.data('typeahead')) return
      if ($this.is('select')) $this.attr('autofocus', true)
      e.preventDefault()
      $this.typeahead($this.data())
  })

}(window.jQuery);
/* ===========================================================
 * bootstrap-inputmask.js j2
 * http://twitter.github.com/bootstrap/javascript.html#tooltips
 * Based on Masked Input plugin by Josh Bush (digitalbush.com)
 * ===========================================================
 * Copyright 2012 Jasny BV, Netherlands.
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */

!function ($) {

  "use strict"; // jshint ;_;

  var isIphone = (window.orientation !== undefined),
      isAndroid = navigator.userAgent.toLowerCase().indexOf("android") > -1


 /* INPUTMASK PUBLIC CLASS DEFINITION
  * ================================= */

  var Inputmask = function (element, options) {
    if (isAndroid) return // No support because caret positioning doesn't work on Android
    
    this.$element = $(element)
    this.options = $.extend({}, $.fn.inputmask.defaults, options)
    this.mask = String(options.mask)
    
    this.init()
    this.listen()
        
    this.checkVal() //Perform initial check for existing values
  }

  Inputmask.prototype = {
    
    init: function() {
      var defs = this.options.definitions
      var len = this.mask.length

      this.tests = [] 
      this.partialPosition = this.mask.length
      this.firstNonMaskPos = null

      $.each(this.mask.split(""), $.proxy(function(i, c) {
        if (c == '?') {
          len--
          this.partialPosition = i
        } else if (defs[c]) {
          this.tests.push(new RegExp(defs[c]))
          if(this.firstNonMaskPos === null)
            this.firstNonMaskPos =  this.tests.length - 1
        } else {
          this.tests.push(null)
        }
      }, this))

      this.buffer = $.map(this.mask.split(""), $.proxy(function(c, i) {
        if (c != '?') return defs[c] ? this.options.placeholder : c
      }, this))
      
      this.focusText = this.$element.val()

      this.$element.data("rawMaskFn", $.proxy(function() {
        return $.map(this.buffer, function(c, i) {
          return this.tests[i] && c != this.options.placeholder ? c : null
        }).join('')
      }, this))
    },
    
    listen: function() {
      if (this.$element.attr("readonly")) return

      var pasteEventName = (navigator.userAgent.match(/msie/i) ? 'paste' : 'input') + ".mask"

      this.$element
        .on("unmask", $.proxy(this.unmask, this))
        
        .on("focus.mask", $.proxy(this.focusEvent, this))
        .on("blur.mask", $.proxy(this.blurEvent, this))
        
        .on("keydown.mask", $.proxy(this.keydownEvent, this))
        .on("keypress.mask", $.proxy(this.keypressEvent, this))

        .on(pasteEventName, $.proxy(this.pasteEvent, this))
    },

    //Helper Function for Caret positioning
    caret: function(begin, end) {
      if (this.$element.length === 0) return
      if (typeof begin == 'number') {
        end = (typeof end == 'number') ? end : begin
        return this.$element.each(function() {
          if (this.setSelectionRange) {
            this.setSelectionRange(begin, end)
          } else if (this.createTextRange) {
            var range = this.createTextRange()
            range.collapse(true)
            range.moveEnd('character', end)
            range.moveStart('character', begin)
            range.select()
          }
        })
      } else {
        if (this.$element[0].setSelectionRange) {
          begin = this.$element[0].selectionStart
          end = this.$element[0].selectionEnd
        } else if (document.selection && document.selection.createRange) {
          var range = document.selection.createRange()
          begin = 0 - range.duplicate().moveStart('character', -100000)
          end = begin + range.text.length
        }
        return {
          begin: begin, 
          end: end
        }
      }
    },
    
    seekNext: function(pos) {
      var len = this.mask.length
      while (++pos <= len && !this.tests[pos]);
      
      return pos
    },
    
    seekPrev: function(pos) {
      while (--pos >= 0 && !this.tests[pos]);
      
      return pos
    },

    shiftL: function(begin,end) {
      var len = this.mask.length
      
      if(begin<0) return
      
      for (var i = begin,j = this.seekNext(end); i < len; i++) {
        if (this.tests[i]) {
          if (j < len && this.tests[i].test(this.buffer[j])) {
            this.buffer[i] = this.buffer[j]
            this.buffer[j] = this.options.placeholder
          } else
            break
          j = this.seekNext(j)
        }
      }
      this.writeBuffer()
      this.caret(Math.max(this.firstNonMaskPos, begin))
    },

    shiftR: function(pos) {
      var len = this.mask.length
      
      for (var i = pos, c = this.options.placeholder; i < len; i++) {
        if (this.tests[i]) {
          var j = this.seekNext(i)
          var t = this.buffer[i]
          this.buffer[i] = c
          if (j < len && this.tests[j].test(t))
            c = t
          else
            break
        }
      }
    },

    unmask: function() {
      this.$element
        .unbind(".mask")
        .removeData("inputmask")
    },
    
    focusEvent: function() {
      this.focusText = this.$element.val()
      var len = this.mask.length 
      var pos = this.checkVal()
      this.writeBuffer()

      var that = this
      var moveCaret = function() {
        if (pos == len)
          that.caret(0, pos)
        else
          that.caret(pos)
      }

      if ($.browser.msie)
        moveCaret()
      else
        setTimeout(moveCaret, 0)
    },
    
    blurEvent: function() {
      this.checkVal()
      if (this.$element.val() != this.focusText)
        this.$element.trigger('change')
    },
        
    keydownEvent: function(e) {
      var k=e.which

      //backspace, delete, and escape get special treatment
      if (k == 8 || k == 46 || (isIphone && k == 127)) {
        var pos = this.caret(),
        begin = pos.begin,
        end = pos.end
						
        if (end-begin === 0) {
          begin = k!=46 ? this.seekPrev(begin) : (end=this.seekNext(begin-1))
          end = k==46 ? this.seekNext(end) : end
        }
        this.clearBuffer(begin, end)
        this.shiftL(begin,end-1)

        return false
      } else if (k == 27) {//escape
        this.$element.val(this.focusText)
        this.caret(0, this.checkVal())
        return false
      }
    },

    keypressEvent: function(e) {
      var len = this.mask.length
      
      var k = e.which,
      pos = this.caret()

      if (e.ctrlKey || e.altKey || e.metaKey || k<32)  {//Ignore
        return true
      } else if (k) {
        if (pos.end - pos.begin !== 0) {
          this.clearBuffer(pos.begin, pos.end)
          this.shiftL(pos.begin, pos.end-1)
        }

        var p = this.seekNext(pos.begin - 1)
        if (p < len) {
          var c = String.fromCharCode(k)
          if (this.tests[p].test(c)) {
            this.shiftR(p)
            this.buffer[p] = c
            this.writeBuffer()
            var next = this.seekNext(p)
            this.caret(next)
          }
        }
        return false
      }
    },

    pasteEvent: function() {
      var that = this
      
      setTimeout(function() {
        that.caret(that.checkVal(true))
      }, 0)
    },
    
    clearBuffer: function(start, end) {
      var len = this.mask.length
      
      for (var i = start; i < end && i < len; i++) {
        if (this.tests[i])
          this.buffer[i] = this.options.placeholder
      }
    },

    writeBuffer: function() {
      return this.$element.val(this.buffer.join('')).val()
    },

    checkVal: function(allow) {
      var len = this.mask.length
      //try to place characters where they belong
      var test = this.$element.val()
      var lastMatch = -1
      
      for (var i = 0, pos = 0; i < len; i++) {
        if (this.tests[i]) {
          this.buffer[i] = this.options.placeholder
          while (pos++ < test.length) {
            var c = test.charAt(pos - 1)
            if (this.tests[i].test(c)) {
              this.buffer[i] = c
              lastMatch = i
              break
            }
          }
          if (pos > test.length)
            break
        } else if (this.buffer[i] == test.charAt(pos) && i != this.partialPosition) {
          pos++
          lastMatch = i
        }
      }
      if (!allow && lastMatch + 1 < this.partialPosition) {
        this.$element.val("")
        this.clearBuffer(0, len)
      } else if (allow || lastMatch + 1 >= this.partialPosition) {
        this.writeBuffer()
        if (!allow) this.$element.val(this.$element.val().substring(0, lastMatch + 1))
      }
      return (this.partialPosition ? i : this.firstNonMaskPos)
    }
  }

  
 /* INPUTMASK PLUGIN DEFINITION
  * =========================== */

  $.fn.inputmask = function (options) {
    return this.each(function () {
      var $this = $(this)
      , data = $this.data('inputmask')
      if (!data) $this.data('inputmask', (data = new Inputmask(this, options)))
    })
  }

  $.fn.inputmask.defaults = {
    mask: "",
    placeholder: "_",
    definitions: {
      '9': "[0-9]",
      'a': "[A-Za-z]",
      '?': "[A-Za-z0-9]",
      '*': "."
    }
  }

  $.fn.inputmask.Constructor = Inputmask


 /* INPUTMASK DATA-API
  * ================== */

  $(document).on('focus.inputmask.data-api', '[data-mask]', function (e) {
    var $this = $(this)
    if ($this.data('inputmask')) return
    e.preventDefault()
    $this.inputmask($this.data())
  })

}(window.jQuery);
/* ============================================================
 * bootstrap-rowlink.js j1
 * http://jasny.github.com/bootstrap/javascript.html#rowlink
 * ============================================================
 * Copyright 2012 Jasny BV, Netherlands.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */

!function ($) {
  
  "use strict"; // jshint ;_;

  var Rowlink = function (element, options) {
    options = $.extend({}, $.fn.rowlink.defaults, options)
    var tr = element.nodeName.toLowerCase() == 'tr' ? $(element) : $(element).find('tr:has(td)')
    
    tr.each(function() {
      var link = $(this).find(options.target).first()
      if (!link.length) return
      
      var href = link.attr('href')

      $(this).find('td').not('.nolink').click(function() {
        window.location = href;
      })

      $(this).addClass('rowlink')
      link.replaceWith(link.html())
    })
  }

  
 /* ROWLINK PLUGIN DEFINITION
  * =========================== */

  $.fn.rowlink = function (options) {
    return this.each(function () {
      var $this = $(this)
      , data = $this.data('rowlink')
      if (!data) $this.data('rowlink', (data = new Rowlink(this, options)))
    })
  }

  $.fn.rowlink.defaults = {
    target: "a"
  }

  $.fn.rowlink.Constructor = Rowlink


 /* ROWLINK DATA-API
  * ================== */

  $(function () {
    $('[data-provides="rowlink"]').each(function () {
      $(this).rowlink($(this).data())
    })
  })
  
}(window.jQuery);
/* ===========================================================
 * bootstrap-fileupload.js j2
 * http://jasny.github.com/bootstrap/javascript.html#fileupload
 * ===========================================================
 * Copyright 2012 Jasny BV, Netherlands.
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */

!function ($) {

  "use strict"; // jshint ;_

 /* FILEUPLOAD PUBLIC CLASS DEFINITION
  * ================================= */

  var Fileupload = function (element, options) {
    this.$element = $(element)
    this.type = this.$element.data('uploadtype') || (this.$element.find('.thumbnail').length > 0 ? "image" : "file")
      
    this.$input = this.$element.find(':file')
    if (this.$input.length === 0) return

    this.name = this.$input.attr('name') || options.name

    this.$hidden = this.$element.find('input[type=hidden][name="'+this.name+'"]')
    if (this.$hidden.length === 0) {
      this.$hidden = $('<input type="hidden" />')
      this.$element.prepend(this.$hidden)
    }

    this.$preview = this.$element.find('.fileupload-preview')
    var height = this.$preview.css('height')
    if (this.$preview.css('display') != 'inline' && height != '0px' && height != 'none') this.$preview.css('line-height', height)

    this.original = {
      'exists': this.$element.hasClass('fileupload-exists'),
      'preview': this.$preview.html(),
      'hiddenVal': this.$hidden.val()
    }
    
    this.$remove = this.$element.find('[data-dismiss="fileupload"]')

    this.$element.find('[data-trigger="fileupload"]').on('click.fileupload', $.proxy(this.trigger, this))

    this.listen()
  }
  
  Fileupload.prototype = {
    
    listen: function() {
      this.$input.on('change.fileupload', $.proxy(this.change, this))
      $(this.$input[0].form).on('reset.fileupload', $.proxy(this.reset, this))
      if (this.$remove) this.$remove.on('click.fileupload', $.proxy(this.clear, this))
    },
    
    change: function(e, invoked) {
      if (invoked === 'clear') return
      
      var file = e.target.files !== undefined ? e.target.files[0] : (e.target.value ? { name: e.target.value.replace(/^.+\\/, '') } : null)
      
      if (!file) {
        this.clear()
        return
      }
      
      this.$hidden.val('')
      this.$hidden.attr('name', '')
      this.$input.attr('name', this.name)

      if (this.type === "image" && this.$preview.length > 0 && (typeof file.type !== "undefined" ? file.type.match('image.*') : file.name.match('\\.(gif|png|jpe?g)$')) && typeof FileReader !== "undefined") {
        var reader = new FileReader()
        var preview = this.$preview
        var element = this.$element

        reader.onload = function(e) {
          preview.html('<img src="' + e.target.result + '" ' + (preview.css('max-height') != 'none' ? 'style="max-height: ' + preview.css('max-height') + ';"' : '') + ' />')
          element.addClass('fileupload-exists').removeClass('fileupload-new')
        }

        reader.readAsDataURL(file)
      } else {
        this.$preview.text(file.name)
        this.$element.addClass('fileupload-exists').removeClass('fileupload-new')
      }
    },

    clear: function(e) {
      this.$hidden.val('')
      this.$hidden.attr('name', this.name)
      this.$input.attr('name', '')

      //ie8+ doesn't support changing the value of input with type=file so clone instead
      if (navigator.userAgent.match(/msie/i)){ 
          var inputClone = this.$input.clone(true);
          this.$input.after(inputClone);
          this.$input.remove();
          this.$input = inputClone;
      }else{
          this.$input.val('')
      }

      this.$preview.html('')
      this.$element.addClass('fileupload-new').removeClass('fileupload-exists')

      if (e) {
        this.$input.trigger('change', [ 'clear' ])
        e.preventDefault()
      }
    },
    
    reset: function(e) {
      this.clear()
      
      this.$hidden.val(this.original.hiddenVal)
      this.$preview.html(this.original.preview)
      
      if (this.original.exists) this.$element.addClass('fileupload-exists').removeClass('fileupload-new')
       else this.$element.addClass('fileupload-new').removeClass('fileupload-exists')
    },
    
    trigger: function(e) {
      this.$input.trigger('click')
      e.preventDefault()
    }
  }

  
 /* FILEUPLOAD PLUGIN DEFINITION
  * =========================== */

  $.fn.fileupload = function (options) {
    return this.each(function () {
      var $this = $(this)
      , data = $this.data('fileupload')
      if (!data) $this.data('fileupload', (data = new Fileupload(this, options)))
      if (typeof options == 'string') data[options]()
    })
  }

  $.fn.fileupload.Constructor = Fileupload


 /* FILEUPLOAD DATA-API
  * ================== */

  $(document).on('click.fileupload.data-api', '[data-provides="fileupload"]', function (e) {
    var $this = $(this)
    if ($this.data('fileupload')) return
    $this.fileupload($this.data())
      
    var $target = $(e.target).closest('[data-dismiss="fileupload"],[data-trigger="fileupload"]');
    if ($target.length > 0) {
      $target.trigger('click.fileupload')
      e.preventDefault()
    }
  })

}(window.jQuery);
/* ==========================================================
 * bootstrap-affix.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#affix
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* AFFIX CLASS DEFINITION
  * ====================== */

  var Affix = function (element, options) {
    this.options = $.extend({}, $.fn.affix.defaults, options)
    this.$window = $(window)
      .on('scroll.affix.data-api', $.proxy(this.checkPosition, this))
      .on('click.affix.data-api',  $.proxy(function () { setTimeout($.proxy(this.checkPosition, this), 1) }, this))
    this.$element = $(element)
    this.checkPosition()
  }

  Affix.prototype.checkPosition = function () {
    if (!this.$element.is(':visible')) return

    var scrollHeight = $(document).height()
      , scrollTop = this.$window.scrollTop()
      , position = this.$element.offset()
      , offset = this.options.offset
      , offsetBottom = offset.bottom
      , offsetTop = offset.top
      , reset = 'affix affix-top affix-bottom'
      , affix

    if (typeof offset != 'object') offsetBottom = offsetTop = offset
    if (typeof offsetTop == 'function') offsetTop = offset.top()
    if (typeof offsetBottom == 'function') offsetBottom = offset.bottom()

    affix = this.unpin != null && (scrollTop + this.unpin <= position.top) ?
      false    : offsetBottom != null && (position.top + this.$element.height() >= scrollHeight - offsetBottom) ?
      'bottom' : offsetTop != null && scrollTop <= offsetTop ?
      'top'    : false

    if (this.affixed === affix) return

    this.affixed = affix
    this.unpin = affix == 'bottom' ? position.top - scrollTop : null

    this.$element.removeClass(reset).addClass('affix' + (affix ? '-' + affix : ''))
  }


 /* AFFIX PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.affix

  $.fn.affix = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('affix')
        , options = typeof option == 'object' && option
      if (!data) $this.data('affix', (data = new Affix(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.affix.Constructor = Affix

  $.fn.affix.defaults = {
    offset: 0
  }


 /* AFFIX NO CONFLICT
  * ================= */

  $.fn.affix.noConflict = function () {
    $.fn.affix = old
    return this
  }


 /* AFFIX DATA-API
  * ============== */

  $(window).on('load', function () {
    $('[data-spy="affix"]').each(function () {
      var $spy = $(this)
        , data = $spy.data()

      data.offset = data.offset || {}

      data.offsetBottom && (data.offset.bottom = data.offsetBottom)
      data.offsetTop && (data.offset.top = data.offsetTop)

      $spy.affix(data)
    })
  })


}(window.jQuery);// ----------------------------------------------------------------------------
// markItUp! Universal MarkUp Engine, JQuery plugin
// v 1.1.x
// Dual licensed under the MIT and GPL licenses.
// ----------------------------------------------------------------------------
// Copyright (C) 2007-2011 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// ----------------------------------------------------------------------------
(function($) {
	$.fn.markItUp = function(settings, extraSettings) {
		var options, ctrlKey, shiftKey, altKey;
		ctrlKey = shiftKey = altKey = false;

		options = {	id:						'',
					nameSpace:				'',
					root:					'',
					previewInWindow:		'', // 'width=800, height=600, resizable=yes, scrollbars=yes'
					previewAutoRefresh:		true,
					previewPosition:		'after',
					previewTemplatePath:	'~/templates/preview.html',
					previewParser:			false,
					previewParserPath:		'',
					previewParserVar:		'data',
					resizeHandle:			true,
					beforeInsert:			'',
					afterInsert:			'',
					onEnter:				{},
					onShiftEnter:			{},
					onCtrlEnter:			{},
					onTab:					{},
					markupSet:			[	{ /* set */ } ]
				};
		$.extend(options, settings, extraSettings);

		// compute markItUp! path
		if (!options.root) {
			$('script').each(function(a, tag) {
				miuScript = $(tag).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);
				if (miuScript !== null) {
					options.root = miuScript[1];
				}
			});
		}

		return this.each(function() {
			var $$, textarea, levels, scrollPosition, caretPosition, caretOffset,
				clicked, hash, header, footer, previewWindow, template, iFrame, abort;
			$$ = $(this);
			textarea = this;
			levels = [];
			abort = false;
			scrollPosition = caretPosition = 0;
			caretOffset = -1;

			options.previewParserPath = localize(options.previewParserPath);
			options.previewTemplatePath = localize(options.previewTemplatePath);

			// apply the computed path to ~/
			function localize(data, inText) {
				if (inText) {
					return 	data.replace(/("|')~\//g, "$1"+options.root);
				}
				return 	data.replace(/^~\//, options.root);
			}

			// init and build editor
			function init() {
				id = ''; nameSpace = '';
				if (options.id) {
					id = 'id="'+options.id+'"';
				} else if ($$.attr("id")) {
					id = 'id="markItUp'+($$.attr("id").substr(0, 1).toUpperCase())+($$.attr("id").substr(1))+'"';

				}
				if (options.nameSpace) {
					nameSpace = 'class="'+options.nameSpace+'"';
				}
				$$.wrap('<div '+nameSpace+'></div>');
				$$.wrap('<div '+id+' class="markItUp"></div>');
				$$.wrap('<div class="markItUpContainer"></div>');
				$$.addClass("markItUpEditor");

				// add the header before the textarea
				header = $('<div class="markItUpHeader"></div>').insertBefore($$);
				$(dropMenus(options.markupSet)).appendTo(header);

				// add the footer after the textarea
				footer = $('<div class="markItUpFooter"></div>').insertAfter($$);

				// add the resize handle after textarea
				if (options.resizeHandle === true && $.browser.safari !== true) {
					resizeHandle = $('<div class="markItUpResizeHandle"></div>')
						.insertAfter($$)
						.bind("mousedown", function(e) {
							var h = $$.height(), y = e.clientY, mouseMove, mouseUp;
							mouseMove = function(e) {
								$$.css("height", Math.max(20, e.clientY+h-y)+"px");
								return false;
							};
							mouseUp = function(e) {
								$("html").unbind("mousemove", mouseMove).unbind("mouseup", mouseUp);
								return false;
							};
							$("html").bind("mousemove", mouseMove).bind("mouseup", mouseUp);
					});
					footer.append(resizeHandle);
				}

				// listen key events
				$$.keydown(keyPressed).keyup(keyPressed);

				// bind an event to catch external calls
				$$.bind("insertion", function(e, settings) {
					if (settings.target !== false) {
						get();
					}
					if (textarea === $.markItUp.focused) {
						markup(settings);
					}
				});

				// remember the last focus
				$$.focus(function() {
					$.markItUp.focused = this;
				});
			}

			// recursively build header with dropMenus from markupset
			function dropMenus(markupSet) {
				var ul = $('<ul></ul>'), i = 0;
				$('li:hover > ul', ul).css('display', 'block');
				$.each(markupSet, function() {
					var button = this, t = '', title, li, j;
					title = (button.key) ? (button.name||'')+' [Ctrl+'+button.key+']' : (button.name||'');
					key   = (button.key) ? 'accesskey="'+button.key+'"' : '';
					if (button.separator) {
						li = $('<li class="markItUpSeparator">'+(button.separator||'')+'</li>').appendTo(ul);
					} else {
						i++;
						for (j = levels.length -1; j >= 0; j--) {
							t += levels[j]+"-";
						}
						li = $('<li class="markItUpButton markItUpButton'+t+(i)+' '+(button.className||'')+'"><a href="" '+key+' title="'+title+'">'+(button.text||button.name||'')+'</a></li>')
						.bind("contextmenu", function() { // prevent contextmenu on mac and allow ctrl+click
							return false;
						}).click(function() {
							return false;
						}).bind("focusin", function(){
                            $$.focus();
						}).mouseup(function() {
							if (button.call) {
								eval(button.call)();
							}
							setTimeout(function() { markup(button) },1);
							return false;
						}).hover(function() {
								$('> ul', this).show();
								$(document).one('click', function() { // close dropmenu if click outside
										$('ul ul', header).hide();
									}
								);
							}, function() {
								$('> ul', this).hide();
							}
						).appendTo(ul);
						if (button.dropMenu) {
							levels.push(i);
							$(li).addClass('markItUpDropMenu').append(dropMenus(button.dropMenu));
						}
					}
				});
				levels.pop();
				return ul;
			}

			// markItUp! markups
			function magicMarkups(string) {
				if (string) {
					string = string.toString();
					string = string.replace(/\(\!\(([\s\S]*?)\)\!\)/g,
						function(x, a) {
							var b = a.split('|!|');
							if (altKey === true) {
								return (b[1] !== undefined) ? b[1] : b[0];
							} else {
								return (b[1] === undefined) ? "" : b[0];
							}
						}
					);
					// [![prompt]!], [![prompt:!:value]!]
					string = string.replace(/\[\!\[([\s\S]*?)\]\!\]/g,
						function(x, a) {
							var b = a.split(':!:');
							if (abort === true) {
								return false;
							}
							value = prompt(b[0], (b[1]) ? b[1] : '');
							if (value === null) {
								abort = true;
							}
							return value;
						}
					);
					return string;
				}
				return "";
			}

			// prepare action
			function prepare(action) {
				if ($.isFunction(action)) {
					action = action(hash);
				}
				return magicMarkups(action);
			}

			// build block to insert
			function build(string) {
				var openWith 			= prepare(clicked.openWith);
				var placeHolder 		= prepare(clicked.placeHolder);
				var replaceWith 		= prepare(clicked.replaceWith);
				var closeWith 			= prepare(clicked.closeWith);
				var openBlockWith 		= prepare(clicked.openBlockWith);
				var closeBlockWith 		= prepare(clicked.closeBlockWith);
				var multiline 			= clicked.multiline;

				if (replaceWith !== "") {
					block = openWith + replaceWith + closeWith;
				} else if (selection === '' && placeHolder !== '') {
					block = openWith + placeHolder + closeWith;
				} else {
					string = string || selection;

					var lines = [string], blocks = [];

					if (multiline === true) {
						lines = string.split(/\r?\n/);
					}

					for (var l = 0; l < lines.length; l++) {
						line = lines[l];
						var trailingSpaces;
						if (trailingSpaces = line.match(/ *$/)) {
							blocks.push(openWith + line.replace(/ *$/g, '') + closeWith + trailingSpaces);
						} else {
							blocks.push(openWith + line + closeWith);
						}
					}

					block = blocks.join("\n");
				}

				block = openBlockWith + block + closeBlockWith;

				return {	block:block,
							openWith:openWith,
							replaceWith:replaceWith,
							placeHolder:placeHolder,
							closeWith:closeWith
					};
			}

			// define markup to insert
			function markup(button) {
				var len, j, n, i;
				hash = clicked = button;
				get();
				$.extend(hash, {	line:"",
						 			root:options.root,
									textarea:textarea,
									selection:(selection||''),
									caretPosition:caretPosition,
									ctrlKey:ctrlKey,
									shiftKey:shiftKey,
									altKey:altKey
								}
							);
				// callbacks before insertion
				prepare(options.beforeInsert);
				prepare(clicked.beforeInsert);
				if ((ctrlKey === true && shiftKey === true) || button.multiline === true) {
					prepare(clicked.beforeMultiInsert);
				}
				$.extend(hash, { line:1 });

				if ((ctrlKey === true && shiftKey === true)) {
					lines = selection.split(/\r?\n/);
					for (j = 0, n = lines.length, i = 0; i < n; i++) {
						if ($.trim(lines[i]) !== '') {
							$.extend(hash, { line:++j, selection:lines[i] } );
							lines[i] = build(lines[i]).block;
						} else {
							lines[i] = "";
						}
					}

					string = { block:lines.join('\n')};
					start = caretPosition;
					len = string.block.length + (($.browser.opera) ? n-1 : 0);
				} else if (ctrlKey === true) {
					string = build(selection);
					start = caretPosition + string.openWith.length;
					len = string.block.length - string.openWith.length - string.closeWith.length;
					len = len - (string.block.match(/ $/) ? 1 : 0);
					len -= fixIeBug(string.block);
				} else if (shiftKey === true) {
					string = build(selection);
					start = caretPosition;
					len = string.block.length;
					len -= fixIeBug(string.block);
				} else {
					string = build(selection);
					start = caretPosition + string.block.length ;
					len = 0;
					start -= fixIeBug(string.block);
				}
				if ((selection === '' && string.replaceWith === '')) {
					caretOffset += fixOperaBug(string.block);

					start = caretPosition + string.openWith.length;
					len = string.block.length - string.openWith.length - string.closeWith.length;

					caretOffset = $$.val().substring(caretPosition,  $$.val().length).length;
					caretOffset -= fixOperaBug($$.val().substring(0, caretPosition));
				}
				$.extend(hash, { caretPosition:caretPosition, scrollPosition:scrollPosition } );

				if (string.block !== selection && abort === false) {
					insert(string.block);
					set(start, len);
				} else {
					caretOffset = -1;
				}
				get();

				$.extend(hash, { line:'', selection:selection });

				// callbacks after insertion
				if ((ctrlKey === true && shiftKey === true) || button.multiline === true) {
					prepare(clicked.afterMultiInsert);
				}
				prepare(clicked.afterInsert);
				prepare(options.afterInsert);

				// refresh preview if opened
				if (previewWindow && options.previewAutoRefresh) {
					refreshPreview();
				}

				// reinit keyevent
				shiftKey = altKey = ctrlKey = abort = false;
			}

			// Substract linefeed in Opera
			function fixOperaBug(string) {
				if ($.browser.opera) {
					return string.length - string.replace(/\n*/g, '').length;
				}
				return 0;
			}
			// Substract linefeed in IE
			function fixIeBug(string) {
				if ($.browser.msie) {
					return string.length - string.replace(/\r*/g, '').length;
				}
				return 0;
			}

			// add markup
			function insert(block) {
				if (document.selection) {
					var newSelection = document.selection.createRange();
					newSelection.text = block;
				} else {
					textarea.value =  textarea.value.substring(0, caretPosition)  + block + textarea.value.substring(caretPosition + selection.length, textarea.value.length);
				}
			}

			// set a selection
			function set(start, len) {
				if (textarea.createTextRange){
					// quick fix to make it work on Opera 9.5
					if ($.browser.opera && $.browser.version >= 9.5 && len == 0) {
						return false;
					}
					range = textarea.createTextRange();
					range.collapse(true);
					range.moveStart('character', start);
					range.moveEnd('character', len);
					range.select();
				} else if (textarea.setSelectionRange ){
					textarea.setSelectionRange(start, start + len);
				}
				textarea.scrollTop = scrollPosition;
				textarea.focus();
			}

			// get the selection
			function get() {
				textarea.focus();

				scrollPosition = textarea.scrollTop;
				if (document.selection) {
					selection = document.selection.createRange().text;
					if ($.browser.msie) { // ie
						var range = document.selection.createRange(), rangeCopy = range.duplicate();
						rangeCopy.moveToElementText(textarea);
						caretPosition = -1;
						while(rangeCopy.inRange(range)) {
							rangeCopy.moveStart('character');
							caretPosition ++;
						}
					} else { // opera
						caretPosition = textarea.selectionStart;
					}
				} else { // gecko & webkit
					caretPosition = textarea.selectionStart;

					selection = textarea.value.substring(caretPosition, textarea.selectionEnd);
				}
				return selection;
			}

			// open preview window
			function preview() {
				if (!previewWindow || previewWindow.closed) {
					if (options.previewInWindow) {
						previewWindow = window.open('', 'preview', options.previewInWindow);
						$(window).unload(function() {
							previewWindow.close();
						});
					} else {
						iFrame = $('<iframe class="markItUpPreviewFrame"></iframe>');
						if (options.previewPosition == 'after') {
							iFrame.insertAfter(footer);
						} else {
							iFrame.insertBefore(header);
						}
						previewWindow = iFrame[iFrame.length - 1].contentWindow || frame[iFrame.length - 1];
					}
				} else if (altKey === true) {
					if (iFrame) {
						iFrame.remove();
					} else {
						previewWindow.close();
					}
					previewWindow = iFrame = false;
				}
				if (!options.previewAutoRefresh) {
					refreshPreview();
				}
				if (options.previewInWindow) {
					previewWindow.focus();
				}
			}

			// refresh Preview window
			function refreshPreview() {
 				renderPreview();
			}

			function renderPreview() {
				var phtml;
				if (options.previewParser && typeof options.previewParser === 'function') {
					var data = options.previewParser( $$.val() );
					writeInPreview( localize(data, 1) );
				} else if (options.previewParserPath !== '') {
					$.ajax({
						type: 'POST',
						dataType: 'text',
						global: false,
						url: options.previewParserPath,
						data: options.previewParserVar+'='+encodeURIComponent($$.val()),
						success: function(data) {
							writeInPreview( localize(data, 1) );
						}
					});
				} else {
					if (!template) {
						$.ajax({
							url: options.previewTemplatePath,
							dataType: 'text',
							global: false,
							success: function(data) {
								writeInPreview( localize(data, 1).replace(/<!-- content -->/g, $$.val()) );
							}
						});
					}
				}
				return false;
			}

			function writeInPreview(data) {
				if (previewWindow.document) {
					try {
						sp = previewWindow.document.documentElement.scrollTop
					} catch(e) {
						sp = 0;
					}
					previewWindow.document.open();
					previewWindow.document.write(data);
					previewWindow.document.close();
					previewWindow.document.documentElement.scrollTop = sp;
				}
			}

			// set keys pressed
			function keyPressed(e) {
				shiftKey = e.shiftKey;
				altKey = e.altKey;
				ctrlKey = (!(e.altKey && e.ctrlKey)) ? (e.ctrlKey || e.metaKey) : false;

				if (e.type === 'keydown') {
					if (ctrlKey === true) {
						li = $('a[accesskey="'+String.fromCharCode(e.keyCode)+'"]', header).parent('li');
						if (li.length !== 0) {
							ctrlKey = false;
							setTimeout(function() {
								li.triggerHandler('mouseup');
							},1);
							return false;
						}
					}
					if (e.keyCode === 13 || e.keyCode === 10) { // Enter key
						if (ctrlKey === true) {  // Enter + Ctrl
							ctrlKey = false;
							markup(options.onCtrlEnter);
							return options.onCtrlEnter.keepDefault;
						} else if (shiftKey === true) { // Enter + Shift
							shiftKey = false;
							markup(options.onShiftEnter);
							return options.onShiftEnter.keepDefault;
						} else { // only Enter
							markup(options.onEnter);
							return options.onEnter.keepDefault;
						}
					}
					if (e.keyCode === 9) { // Tab key
						if (shiftKey == true || ctrlKey == true || altKey == true) {
							return false;
						}
						if (caretOffset !== -1) {
							get();
							caretOffset = $$.val().length - caretOffset;
							set(caretOffset, 0);
							caretOffset = -1;
							return false;
						} else {
							markup(options.onTab);
							return options.onTab.keepDefault;
						}
					}
				}
			}

			init();
		});
	};

	$.fn.markItUpRemove = function() {
		return this.each(function() {
				var $$ = $(this).unbind().removeClass('markItUpEditor');
				$$.parent('div').parent('div.markItUp').parent('div').replaceWith($$);
			}
		);
	};

	$.markItUp = function(settings) {
		var options = { target:false };
		$.extend(options, settings);
		if (options.target) {
			return $(options.target).each(function() {
				$(this).focus();
				$(this).trigger('insertion', [options]);
			});
		} else {
			$('textarea').trigger('insertion', [options]);
		}
	};
})(jQuery);
bbCodeSettings = {
	nameSpace: 'bbcode',
	onTab: { keepDefault: false, replaceWith: '	' },
	//previewParserPath: 	'', // path to your BBCode parser
	markupSet:  [
		{ name: 'Bold', text: '<i class="icon-bold"></i>', className: 'btn btn-link button-bold', key: 'B', openWith: '[b]', closeWith: '[/b]' },
		{ name: 'Italic', text: '<i class="icon-italic"></i>', className: 'btn btn-link button-italic', key: 'I', openWith: '[i]', closeWith: '[/i]' },
//		{ name: 'Underline', className: 'btn btn-small button-underline', key: 'U', openWith: '[u]', closeWith: '[/u]' },
//		{ name: 'Size', text: '<i class="icon-text-height"></i>', className: 'btn btn-link button-size', key: 'S', openWith: '[size=[![Text size]!]]', closeWith: '[/size]',
//			dropMenu : [
//				{ name: 'Small', text: '<i class="icon-zoom-in"></i>', className: 'btn btn-linkbutton-small', openWith: '[size=50]', closeWith: '[/size]' },
//				{ name: 'Normal', text: '<i class="icon-font"></i>', className: 'btn btn-linkbutton-normal', openWith: '[size=100]', closeWith: '[/size]' },
//				{ name: 'Big', text: '<i class="icon-zoom-out"></i>', className: 'btn btn-linkbutton-big', openWith: '[size=200]', closeWith: '[/size]' }
//			]},
//		{ name: 'Small', text: '<i class="icon-minus"></i>', className: 'btn btn-link button-small', openWith: '[size=50]', closeWith: '[/size]' },
//		{ name: 'Big', text: '<i class="icon-plus"></i>', className: 'btn btn-link button-big', openWith: '[size=200]', closeWith: '[/size]' },
//		{ separator: '&bull;' },
		{ name: 'Picture', text: '<i class="icon-picture"></i>', className: 'btn btn-link button-picture', key: 'P', replaceWith: '[img][![URL]!][/img]' },
		{ name: 'Link', text: '<i class="icon-link"></i>', className: 'btn btn-link button-link', key: 'L', openWith: '[url=[![URL]!]]', closeWith: '[/url]', placeHolder: 'http://' },
//		{ separator: '&bull;' },
		{ name: 'Align left', text: '<i class="icon-align-left"></i>', className: 'btn btn-link button-align-left', openWith: '[left]', closeWith: '[/left]' },
		{ name: 'Align center', text: '<i class="icon-align-center"></i>', className: 'btn btn-link button-align-center', openWith: '[center]', closeWith: '[/center]' },
		{ name: 'Align right', text: '<i class="icon-align-right"></i>', className: 'btn btn-link button-align-right', openWith: '[right]', closeWith: '[/right]' },
		{ name: 'Bulleted list', text: '<i class="icon-list-ul"></i>', className: 'btn btn-link button-list-bullets', openWith: '[list]\n[*]', closeWith: '\n[/list]' },
		{ name: 'Numbered list', text: '<i class="icon-list-ol"></i>', className: 'btn btn-link button-list-numbers', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]' },
//		{ name: 'List item',     text: '<i class="icon-bold"></i>', className: 'btn btn-link button-list-item',    openWith: '[*] ' },
//		{ separator: '&bull;' },
//		{ name: 'Quote', text: '<i class="icon-leaf"></i>', className: 'btn btn-link button-quote', openWith: '[quote]', closeWith: '[/quote]' },
//		{ name: 'Code',  className: 'btn btn-link button-code',  openWith: '[code]', closeWith: '[/code]' },
//		{ separator: '&bull;' },
//		{ name: 'Remove BBCode', text: '<i class="icon-trash icon-white"></i>', className: 'btn btn-link button-clear', replaceWith: function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, ''); } }
//		{ name: 'Preview', className: "preview", call: 'preview' }
		{ name: 'Smileys', text: '<i class="icon-smile"></i>', className: 'btn btn-link button-list-numbers', beforeInsert: function(cb) {
			var $smileys = $(cb.textarea).closest('form').find('.smileys');

			if ($smileys.length) {
				$smileys.find('.lazy').lazyload({
					event:          'lazy',
					skip_invisible: false
				});

				$smileys
					.on('shown', function() {
						$smileys.find('.lazy').trigger('lazy');
					})
					.collapse('toggle');
			}
		} }
	]
};
/*
 * imgAreaSelect jQuery plugin
 * version 0.9.3
 *
 * Copyright (c) 2008-2010 Michal Wojciechowski (odyniec.net)
 *
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://odyniec.net/projects/imgareaselect/
 *
 */

(function($) {

var abs = Math.abs,
    max = Math.max,
    min = Math.min,
    round = Math.round;

function div() {
    return $('<div/>');
}

$.imgAreaSelect = function (img, options) {
    var

        $img = $(img),

        imgLoaded,

        $box = div(),
        $area = div(),
        $border = div().add(div()).add(div()).add(div()),
        $outer = div().add(div()).add(div()).add(div()),
        $handles = $([]),

        $areaOpera,

        left, top,

        imgOfs,

        imgWidth, imgHeight,

        $parent,

        parOfs,

        zIndex = 0,

        position = 'absolute',

        startX, startY,

        scaleX, scaleY,

        resizeMargin = 10,

        resize,

        minWidth, minHeight, maxWidth, maxHeight,

        aspectRatio,

        shown,

        x1, y1, x2, y2,

        selection = { x1: 0, y1: 0, x2: 0, y2: 0, width: 0, height: 0 },

        docElem = document.documentElement,

        $p, d, i, o, w, h, adjusted;

    function viewX(x) {
        return x + imgOfs.left - parOfs.left;
    }

    function viewY(y) {
        return y + imgOfs.top - parOfs.top;
    }

    function selX(x) {
        return x - imgOfs.left + parOfs.left;
    }

    function selY(y) {
        return y - imgOfs.top + parOfs.top;
    }

    function evX(event) {
        return event.pageX - parOfs.left;
    }

    function evY(event) {
        return event.pageY - parOfs.top;
    }

    function getSelection(noScale) {
        var sx = noScale || scaleX, sy = noScale || scaleY;

        return { x1: round(selection.x1 * sx),
            y1: round(selection.y1 * sy),
            x2: round(selection.x2 * sx),
            y2: round(selection.y2 * sy),
            width: round(selection.x2 * sx) - round(selection.x1 * sx),
            height: round(selection.y2 * sy) - round(selection.y1 * sy) };
    }

    function setSelection(x1, y1, x2, y2, noScale) {
        var sx = noScale || scaleX, sy = noScale || scaleY;

        selection = {
            x1: round(x1 / sx),
            y1: round(y1 / sy),
            x2: round(x2 / sx),
            y2: round(y2 / sy)
        };

        selection.width = selection.x2 - selection.x1;
        selection.height = selection.y2 - selection.y1;
    }

    function adjust() {
        if (!$img.width())
            return;

        imgOfs = { left: round($img.offset().left), top: round($img.offset().top) };

        imgWidth = $img.width();
        imgHeight = $img.height();

        minWidth = options.minWidth || 0;
        minHeight = options.minHeight || 0;
        maxWidth = min(options.maxWidth || 1<<24, imgWidth);
        maxHeight = min(options.maxHeight || 1<<24, imgHeight);

        if ($().jquery == '1.3.2' && position == 'fixed' &&
            !docElem['getBoundingClientRect'])
        {
            imgOfs.top += max(document.body.scrollTop, docElem.scrollTop);
            imgOfs.left += max(document.body.scrollLeft, docElem.scrollLeft);
        }

        parOfs = $.inArray($parent.css('position'), ['absolute', 'relative']) + 1 ?
            { left: round($parent.offset().left) - $parent.scrollLeft(),
                top: round($parent.offset().top) - $parent.scrollTop() } :
            position == 'fixed' ?
                { left: $(document).scrollLeft(), top: $(document).scrollTop() } :
                { left: 0, top: 0 };

        left = viewX(0);
        top = viewY(0);

        if (selection.x2 > imgWidth || selection.y2 > imgHeight)
            doResize();
    }

    function update(resetKeyPress) {
        if (!shown) return;

        $box.css({ left: viewX(selection.x1), top: viewY(selection.y1) })
            .add($area).width(w = selection.width).height(h = selection.height);

        $area.add($border).add($handles).css({ left: 0, top: 0 });

        $border
            .width(max(w - $border.outerWidth() + $border.innerWidth(), 0))
            .height(max(h - $border.outerHeight() + $border.innerHeight(), 0));

        $($outer[0]).css({ left: left, top: top,
            width: selection.x1, height: imgHeight });
        $($outer[1]).css({ left: left + selection.x1, top: top,
            width: w, height: selection.y1 });
        $($outer[2]).css({ left: left + selection.x2, top: top,
            width: imgWidth - selection.x2, height: imgHeight });
        $($outer[3]).css({ left: left + selection.x1, top: top + selection.y2,
            width: w, height: imgHeight - selection.y2 });

        w -= $handles.outerWidth();
        h -= $handles.outerHeight();

        switch ($handles.length) {
        case 8:
            $($handles[4]).css({ left: w / 2 });
            $($handles[5]).css({ left: w, top: h / 2 });
            $($handles[6]).css({ left: w / 2, top: h });
            $($handles[7]).css({ top: h / 2 });
        case 4:
            $handles.slice(1,3).css({ left: w });
            $handles.slice(2,4).css({ top: h });
        }

        if (resetKeyPress !== false) {
            if ($.imgAreaSelect.keyPress != docKeyPress)
                $(document).unbind($.imgAreaSelect.keyPress,
                    $.imgAreaSelect.onKeyPress);

            if (options.keys)
                $(document)[$.imgAreaSelect.keyPress](
                    $.imgAreaSelect.onKeyPress = docKeyPress);
        }

        if ($.browser.msie && $border.outerWidth() - $border.innerWidth() == 2) {
            $border.css('margin', 0);
            setTimeout(function () { $border.css('margin', 'auto'); }, 0);
        }
    }

    function doUpdate(resetKeyPress) {
        adjust();
        update(resetKeyPress);
        x1 = viewX(selection.x1); y1 = viewY(selection.y1);
        x2 = viewX(selection.x2); y2 = viewY(selection.y2);
    }

    function hide($elem, fn) {
        options.fadeSpeed ? $elem.fadeOut(options.fadeSpeed, fn) : $elem.hide();

    }

    function areaMouseMove(event) {
        var x = selX(evX(event)) - selection.x1,
            y = selY(evY(event)) - selection.y1;

        if (!adjusted) {
            adjust();
            adjusted = true;

            $box.one('mouseout', function () { adjusted = false; });
        }

        resize = '';

        if (options.resizable) {
            if (y <= resizeMargin)
                resize = 'n';
            else if (y >= selection.height - resizeMargin)
                resize = 's';
            if (x <= resizeMargin)
                resize += 'w';
            else if (x >= selection.width - resizeMargin)
                resize += 'e';
        }

        $box.css('cursor', resize ? resize + '-resize' :
            options.movable ? 'move' : '');
        if ($areaOpera)
            $areaOpera.toggle();
    }

    function docMouseUp(event) {
        $('body').css('cursor', '');

        if (options.autoHide || selection.width * selection.height == 0)
            hide($box.add($outer), function () { $(this).hide(); });

        options.onSelectEnd(img, getSelection());

        $(document).unbind('mousemove', selectingMouseMove);
        $box.mousemove(areaMouseMove);
    }

    function areaMouseDown(event) {
        if (event.which != 1) return false;

        adjust();

        if (resize) {
            $('body').css('cursor', resize + '-resize');

            x1 = viewX(selection[/w/.test(resize) ? 'x2' : 'x1']);
            y1 = viewY(selection[/n/.test(resize) ? 'y2' : 'y1']);

            $(document).mousemove(selectingMouseMove)
                .one('mouseup', docMouseUp);
            $box.unbind('mousemove', areaMouseMove);
        }
        else if (options.movable) {
            startX = left + selection.x1 - evX(event);
            startY = top + selection.y1 - evY(event);

            $box.unbind('mousemove', areaMouseMove);

            $(document).mousemove(movingMouseMove)
                .one('mouseup', function () {
                    options.onSelectEnd(img, getSelection());

                    $(document).unbind('mousemove', movingMouseMove);
                    $box.mousemove(areaMouseMove);
                });
        }
        else
            $img.mousedown(event);

        return false;
    }

    function fixAspectRatio(xFirst) {
        if (aspectRatio)
            if (xFirst) {
                x2 = max(left, min(left + imgWidth,
                    x1 + abs(y2 - y1) * aspectRatio * (x2 > x1 || -1)));

                y2 = round(max(top, min(top + imgHeight,
                    y1 + abs(x2 - x1) / aspectRatio * (y2 > y1 || -1))));
                x2 = round(x2);
            }
            else {
                y2 = max(top, min(top + imgHeight,
                    y1 + abs(x2 - x1) / aspectRatio * (y2 > y1 || -1)));
                x2 = round(max(left, min(left + imgWidth,
                    x1 + abs(y2 - y1) * aspectRatio * (x2 > x1 || -1))));
                y2 = round(y2);
            }
    }

    function doResize() {
        x1 = min(x1, left + imgWidth);
        y1 = min(y1, top + imgHeight);

        if (abs(x2 - x1) < minWidth) {
            x2 = x1 - minWidth * (x2 < x1 || -1);

            if (x2 < left)
                x1 = left + minWidth;
            else if (x2 > left + imgWidth)
                x1 = left + imgWidth - minWidth;
        }

        if (abs(y2 - y1) < minHeight) {
            y2 = y1 - minHeight * (y2 < y1 || -1);

            if (y2 < top)
                y1 = top + minHeight;
            else if (y2 > top + imgHeight)
                y1 = top + imgHeight - minHeight;
        }

        x2 = max(left, min(x2, left + imgWidth));
        y2 = max(top, min(y2, top + imgHeight));

        fixAspectRatio(abs(x2 - x1) < abs(y2 - y1) * aspectRatio);

        if (abs(x2 - x1) > maxWidth) {
            x2 = x1 - maxWidth * (x2 < x1 || -1);
            fixAspectRatio();
        }

        if (abs(y2 - y1) > maxHeight) {
            y2 = y1 - maxHeight * (y2 < y1 || -1);
            fixAspectRatio(true);
        }

        selection = { x1: selX(min(x1, x2)), x2: selX(max(x1, x2)),
            y1: selY(min(y1, y2)), y2: selY(max(y1, y2)),
            width: abs(x2 - x1), height: abs(y2 - y1) };

        update();

        options.onSelectChange(img, getSelection());
    }

    function selectingMouseMove(event) {
        x2 = resize == '' || /w|e/.test(resize) || aspectRatio ? evX(event) : viewX(selection.x2);
        y2 = resize == '' || /n|s/.test(resize) || aspectRatio ? evY(event) : viewY(selection.y2);

        doResize();

        return false;

    }

    function doMove(newX1, newY1) {
        x2 = (x1 = newX1) + selection.width;
        y2 = (y1 = newY1) + selection.height;

        $.extend(selection, { x1: selX(x1), y1: selY(y1), x2: selX(x2),
            y2: selY(y2) });

        update();

        options.onSelectChange(img, getSelection());
    }

    function movingMouseMove(event) {
        x1 = max(left, min(startX + evX(event), left + imgWidth - selection.width));
        y1 = max(top, min(startY + evY(event), top + imgHeight - selection.height));

        doMove(x1, y1);

        event.preventDefault();

        return false;
    }

    function startSelection() {
        adjust();

        x2 = x1;
        y2 = y1;

        doResize();

        resize = '';

        if ($outer.is(':not(:visible)'))
            $box.add($outer).hide().fadeIn(options.fadeSpeed||0);

        shown = true;

        $(document).unbind('mouseup', cancelSelection)
            .mousemove(selectingMouseMove).one('mouseup', docMouseUp);
        $box.unbind('mousemove', areaMouseMove);

        options.onSelectStart(img, getSelection());
    }

    function cancelSelection() {
        $(document).unbind('mousemove', startSelection);
        hide($box.add($outer));

        selection = { x1: selX(x1), y1: selY(y1), x2: selX(x1), y2: selY(y1),
                width: 0, height: 0 };

        options.onSelectChange(img, getSelection());
        options.onSelectEnd(img, getSelection());
    }

    function imgMouseDown(event) {
        if (event.which != 1 || $outer.is(':animated')) return false;

        adjust();
        startX = x1 = evX(event);
        startY = y1 = evY(event);

        $(document).one('mousemove', startSelection)
            .one('mouseup', cancelSelection);

        return false;
    }

    function windowResize() {
        doUpdate(false);
    }

    function imgLoad() {
        imgLoaded = true;

        setOptions(options = $.extend({
            classPrefix: 'imgareaselect',
            movable: true,
            resizable: true,
            parent: 'body',
            onInit: function () {},
            onSelectStart: function () {},
            onSelectChange: function () {},
            onSelectEnd: function () {}
        }, options));

        $box.add($outer).css({ visibility: '' });

        if (options.show) {
            shown = true;
            adjust();
            update();
            $box.add($outer).hide().fadeIn(options.fadeSpeed||0);
        }

        setTimeout(function () { options.onInit(img, getSelection()); }, 0);
    }

    var docKeyPress = function(event) {
        var k = options.keys, d, t, key = event.keyCode;

        d = !isNaN(k.alt) && (event.altKey || event.originalEvent.altKey) ? k.alt :
            !isNaN(k.ctrl) && event.ctrlKey ? k.ctrl :
            !isNaN(k.shift) && event.shiftKey ? k.shift :
            !isNaN(k.arrows) ? k.arrows : 10;

        if (k.arrows == 'resize' || (k.shift == 'resize' && event.shiftKey) ||
            (k.ctrl == 'resize' && event.ctrlKey) ||
            (k.alt == 'resize' && (event.altKey || event.originalEvent.altKey)))
        {
            switch (key) {
            case 37:
                d = -d;
            case 39:
                t = max(x1, x2);
                x1 = min(x1, x2);
                x2 = max(t + d, x1);
                fixAspectRatio();
                break;
            case 38:
                d = -d;
            case 40:
                t = max(y1, y2);
                y1 = min(y1, y2);
                y2 = max(t + d, y1);
                fixAspectRatio(true);
                break;
            default:
                return;
            }

            doResize();
        }
        else {
            x1 = min(x1, x2);
            y1 = min(y1, y2);

            switch (key) {
            case 37:
                doMove(max(x1 - d, left), y1);
                break;
            case 38:
                doMove(x1, max(y1 - d, top));
                break;
            case 39:
                doMove(x1 + min(d, imgWidth - selX(x2)), y1);
                break;
            case 40:
                doMove(x1, y1 + min(d, imgHeight - selY(y2)));
                break;
            default:
                return;
            }
        }

        return false;
    };

    function styleOptions($elem, props) {
        for (option in props)
            if (options[option] !== undefined)
                $elem.css(props[option], options[option]);
    }

    function setOptions(newOptions) {
        if (newOptions.parent)
            ($parent = $(newOptions.parent)).append($box.add($outer));

        $.extend(options, newOptions);

        adjust();

        if (newOptions.handles != null) {
            $handles.remove();
            $handles = $([]);

            i = newOptions.handles ? newOptions.handles == 'corners' ? 4 : 8 : 0;

            while (i--)
                $handles = $handles.add(div());

            $handles.addClass(options.classPrefix + '-handle').css({
                position: 'absolute',
                fontSize: 0,
                zIndex: zIndex + 1 || 1
            });

            if (!parseInt($handles.css('width')) >= 0)
                $handles.width(5).height(5);

            if (o = options.borderWidth)
                $handles.css({ borderWidth: o, borderStyle: 'solid' });

            styleOptions($handles, { borderColor1: 'border-color',
                borderColor2: 'background-color',
                borderOpacity: 'opacity' });
        }

        scaleX = options.imageWidth / imgWidth || 1;
        scaleY = options.imageHeight / imgHeight || 1;

        if (newOptions.x1 != null) {
            setSelection(newOptions.x1, newOptions.y1, newOptions.x2,
                newOptions.y2);
            newOptions.show = !newOptions.hide;
        }

        if (newOptions.keys)
            options.keys = $.extend({ shift: 1, ctrl: 'resize' },
                newOptions.keys);

        $outer.addClass(options.classPrefix + '-outer');
        $area.addClass(options.classPrefix + '-selection');
        for (i = 0; i++ < 4;)
            $($border[i-1]).addClass(options.classPrefix + '-border' + i);

        styleOptions($area, { selectionColor: 'background-color',
            selectionOpacity: 'opacity' });
        styleOptions($border, { borderOpacity: 'opacity',
            borderWidth: 'border-width' });
        styleOptions($outer, { outerColor: 'background-color',
            outerOpacity: 'opacity' });
        if (o = options.borderColor1)
            $($border[0]).css({ borderStyle: 'solid', borderColor: o });
        if (o = options.borderColor2)
            $($border[1]).css({ borderStyle: 'dashed', borderColor: o });

        $box.append($area.add($border).add($handles).add($areaOpera));

        if ($.browser.msie) {
            if (o = $outer.css('filter').match(/opacity=([0-9]+)/))
                $outer.css('opacity', o[1]/100);
            if (o = $border.css('filter').match(/opacity=([0-9]+)/))
                $border.css('opacity', o[1]/100);
        }

        if (newOptions.hide)
            hide($box.add($outer));
        else if (newOptions.show && imgLoaded) {
            shown = true;
            $box.add($outer).fadeIn(options.fadeSpeed||0);
            doUpdate();
        }

        aspectRatio = (d = (options.aspectRatio || '').split(/:/))[0] / d[1];

        $img.add($outer).unbind('mousedown', imgMouseDown);

        if (options.disable || options.enable === false) {
            $box.unbind('mousemove', areaMouseMove).unbind('mousedown', areaMouseDown);
            $(window).unbind('resize', windowResize);
        }
        else {
            if (options.enable || options.disable === false) {
                if (options.resizable || options.movable)
                    $box.mousemove(areaMouseMove).mousedown(areaMouseDown);

                $(window).resize(windowResize);
            }

            if (!options.persistent)
                $img.add($outer).mousedown(imgMouseDown);
        }

        options.enable = options.disable = undefined;
    }

    this.remove = function () {
        $img.unbind('mousedown', imgMouseDown);
        $box.add($outer).remove();
    };

    this.getOptions = function () { return options; };

    this.setOptions = setOptions;

    this.getSelection = getSelection;

    this.setSelection = setSelection;

    this.update = doUpdate;

    $p = $img;

    while ($p.length) {
        zIndex = max(zIndex,
            !isNaN($p.css('z-index')) ? $p.css('z-index') : zIndex);
        if ($p.css('position') == 'fixed')
            position = 'fixed';

        $p = $p.parent(':not(body)');
    }

    zIndex = options.zIndex || zIndex;

    if ($.browser.msie)
        $img.attr('unselectable', 'on');

    $.imgAreaSelect.keyPress = $.browser.msie ||
        $.browser.safari ? 'keydown' : 'keypress';

    if ($.browser.opera)
        $areaOpera = div().css({ width: '100%', height: '100%',
            position: 'absolute', zIndex: zIndex + 2 || 2 });

    $box.add($outer).css({ visibility: 'hidden', position: position,
        overflow: 'hidden', zIndex: zIndex || '0' });
    $box.css({ zIndex: zIndex + 2 || 2 });
    $area.add($border).css({ position: 'absolute', fontSize: 0 });

    img.complete || img.readyState == 'complete' || !$img.is('img') ?
        imgLoad() : $img.one('load', imgLoad);
};

$.fn.imgAreaSelect = function (options) {
    options = options || {};

    this.each(function () {
        if ($(this).data('imgAreaSelect')) {
            if (options.remove) {
                $(this).data('imgAreaSelect').remove();
                $(this).removeData('imgAreaSelect');
            }
            else
                $(this).data('imgAreaSelect').setOptions(options);
        }
        else if (!options.remove) {
            if (options.enable === undefined && options.disable === undefined)
                options.enable = true;

            $(this).data('imgAreaSelect', new $.imgAreaSelect(this, options));
        }
    });

    if (options.instance)
        return $(this).data('imgAreaSelect');

    return this;
};

})(jQuery);
/*
 * Lazy Load - jQuery plugin for lazy loading images
 *
 * Copyright (c) 2007-2013 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/lazyload
 *
 * Version:  1.8.4
 *
 */
(function($, window, document, undefined) {
    var $window = $(window);

    $.fn.lazyload = function(options) {
        var elements = this;
        var $container;
        var settings = {
            threshold       : 0,
            failure_limit   : 0,
            event           : "scroll",
            effect          : "show",
            container       : window,
            data_attribute  : "original",
            skip_invisible  : true,
            appear          : null,
            load            : null
        };

        function update() {
            var counter = 0;
      
            elements.each(function() {
                var $this = $(this);
                if (settings.skip_invisible && !$this.is(":visible")) {
                    return;
                }
                if ($.abovethetop(this, settings) ||
                    $.leftofbegin(this, settings)) {
                        /* Nothing. */
                } else if (!$.belowthefold(this, settings) &&
                    !$.rightoffold(this, settings)) {
                        $this.trigger("appear");
                        /* if we found an image we'll load, reset the counter */
                        counter = 0;
                } else {
                    if (++counter > settings.failure_limit) {
                        return false;
                    }
                }
            });

        }

        if(options) {
            /* Maintain BC for a couple of versions. */
            if (undefined !== options.failurelimit) {
                options.failure_limit = options.failurelimit; 
                delete options.failurelimit;
            }
            if (undefined !== options.effectspeed) {
                options.effect_speed = options.effectspeed; 
                delete options.effectspeed;
            }

            $.extend(settings, options);
        }

        /* Cache container as jQuery as object. */
        $container = (settings.container === undefined ||
                      settings.container === window) ? $window : $(settings.container);

        /* Fire one scroll event per scroll. Not one scroll event per image. */
        if (0 === settings.event.indexOf("scroll")) {
            $container.bind(settings.event, function(event) {
                return update();
            });
        }

        this.each(function() {
            var self = this;
            var $self = $(self);

            self.loaded = false;

            /* When appear is triggered load original image. */
            $self.one("appear", function() {
                if (!this.loaded) {
                    if (settings.appear) {
                        var elements_left = elements.length;
                        settings.appear.call(self, elements_left, settings);
                    }
                    $("<img />")
                        .bind("load", function() {
                            $self
                                .hide()
                                .attr("src", $self.data(settings.data_attribute))
                                [settings.effect](settings.effect_speed);
                            self.loaded = true;

                            /* Remove image from array so it is not looped next time. */
                            var temp = $.grep(elements, function(element) {
                                return !element.loaded;
                            });
                            elements = $(temp);

                            if (settings.load) {
                                var elements_left = elements.length;
                                settings.load.call(self, elements_left, settings);
                            }
                        })
                        .attr("src", $self.data(settings.data_attribute));
                }
            });

            /* When wanted event is triggered load original image */
            /* by triggering appear.                              */
            if (0 !== settings.event.indexOf("scroll")) {
                $self.bind(settings.event, function(event) {
                    if (!self.loaded) {
                        $self.trigger("appear");
                    }
                });
            }
        });

        /* Check if something appears when window is resized. */
        $window.bind("resize", function(event) {
            update();
        });
              
        /* With IOS5 force loading images when navigating with back button. */
        /* Non optimal workaround. */
        if ((/iphone|ipod|ipad.*os 5/gi).test(navigator.appVersion)) {
            $window.bind("pageshow", function(event) {
                if (event.originalEvent.persisted) {
                    elements.each(function() {
                        $(this).trigger("appear");
                    });
                }
            });
        }

        /* Force initial check if images should appear. */
        $(window).load(function() {
            update();
        });
        
        return this;
    };

    /* Convenience methods in jQuery namespace.           */
    /* Use as  $.belowthefold(element, {threshold : 100, container : window}) */

    $.belowthefold = function(element, settings) {
        var fold;
        
        if (settings.container === undefined || settings.container === window) {
            fold = $window.height() + $window.scrollTop();
        } else {
            fold = $(settings.container).offset().top + $(settings.container).height();
        }

        return fold <= $(element).offset().top - settings.threshold;
    };
    
    $.rightoffold = function(element, settings) {
        var fold;

        if (settings.container === undefined || settings.container === window) {
            fold = $window.width() + $window.scrollLeft();
        } else {
            fold = $(settings.container).offset().left + $(settings.container).width();
        }

        return fold <= $(element).offset().left - settings.threshold;
    };
        
    $.abovethetop = function(element, settings) {
        var fold;
        
        if (settings.container === undefined || settings.container === window) {
            fold = $window.scrollTop();
        } else {
            fold = $(settings.container).offset().top;
        }

        return fold >= $(element).offset().top + settings.threshold  + $(element).height();
    };
    
    $.leftofbegin = function(element, settings) {
        var fold;
        
        if (settings.container === undefined || settings.container === window) {
            fold = $window.scrollLeft();
        } else {
            fold = $(settings.container).offset().left;
        }

        return fold >= $(element).offset().left + settings.threshold + $(element).width();
    };

    $.inviewport = function(element, settings) {
         return !$.rightoffold(element, settings) && !$.leftofbegin(element, settings) &&
                !$.belowthefold(element, settings) && !$.abovethetop(element, settings);
     };

    /* Custom selectors for your convenience.   */
    /* Use as $("img:below-the-fold").something() or */
    /* $("img").filter(":below-the-fold").something() which is faster */

    $.extend($.expr[':'], {
        "below-the-fold" : function(a) { return $.belowthefold(a, {threshold : 0}); },
        "above-the-top"  : function(a) { return !$.belowthefold(a, {threshold : 0}); },
        "right-of-screen": function(a) { return $.rightoffold(a, {threshold : 0}); },
        "left-of-screen" : function(a) { return !$.rightoffold(a, {threshold : 0}); },
        "in-viewport"    : function(a) { return $.inviewport(a, {threshold : 0}); },
        /* Maintain BC for couple of versions. */
        "above-the-fold" : function(a) { return !$.belowthefold(a, {threshold : 0}); },
        "right-of-fold"  : function(a) { return $.rightoffold(a, {threshold : 0}); },
        "left-of-fold"   : function(a) { return !$.rightoffold(a, {threshold : 0}); }
    });

})(jQuery, window, document);
