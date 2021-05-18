
function stickyButton() {

  const wrap = document.querySelector(".btn-wrap");
  const container = document.querySelector(".btn-container");
  const wrapTop = wrap.getBoundingClientRect().top + pageYOffset;
  const screenHeight = document.documentElement.clientHeight;


  if(pageYOffset >= (wrapTop - screenHeight)) {
    container.classList.remove('fixed');
  } else {
    container.classList.add('fixed');
  }  
}

window.addEventListener("scroll", stickyButton);



const animation = {
  animationList: ["slide-left", "slide-right", "slide-right-abs-h", "slide-left-abs-h", "slide-bottom-abs-v", "slide-bottom-abs-v-2", "fade"],
  wrappers: document.querySelectorAll(".animation-wrapper-js"),

  toArr: function(list) {
    const arr = [];

    list.forEach(function(item) {
        arr.push(item);
    });

    return arr;
  },

  getAnimatedElements: function(elements) {
    const arr = [];

      for(let k = 0; k < elements.length; k++) {
        if(elements[k].classList.contains("animated-block-js")) {
          arr.push(elements[k]);
        }
      }

      return arr;
  },

  addAnimationClass: function() {
    const self = this;
    const wrappersArr = this.toArr(this.wrappers);

    function addClass(elements, anim) {
      anim.forEach(function(animationName) {
        elements.forEach(function(el, j) {
          if(el.classList.contains(animationName)) {
              el.classList.add(animationName + "-in");
          }
        });
      });
    }
    
    window.addEventListener("scroll", function() {
      wrappersArr.forEach(function(item) {
        const topIndent = item.getBoundingClientRect().top;

        if(topIndent > 0 && topIndent < 600 || topIndent < 0) {  
            const animatedElements = self.getAnimatedElements(item.getElementsByTagName('*'));
            addClass(animatedElements, self.animationList);
        }
      });
    });
  },

  removeAnimationsClass: function() {
    const self = this;
    const wrappersArr = this.toArr(this.wrappers);

    function removeClass(elements, anim) {
      anim.forEach(function(animationName) {
        elements.forEach(function(el, j) {
          if(el.classList.contains(animationName)) {
              el.classList.remove(animationName);
          }
        });
      });
    }

    wrappersArr.forEach(function(item) {
        const animatedElements = self.getAnimatedElements(item.getElementsByTagName('*'));
        removeClass(animatedElements, self.animationList);
    });
  },

  init: function() {
    if(document.documentElement.clientWidth > 768) {
        this.addAnimationClass();
    } else {
        this.removeAnimationsClass();
    }
  },

  initOnResize: function() {
    this.init();

    const self = this;

    window.addEventListener("resize", function() {
        self.init()        
    });
  }
  
}

animation.initOnResize();

(function setLinkLabel() {
  var loc  = window.location.href;
  var labels = loc.split("?")[1];

  var links = document.querySelectorAll("a");

  if(links && labels) {
    links.forEach(function(el, i) {
      var originLink = el.getAttribute("href");
      var fullLink = originLink + "?" + labels;
      
      el.setAttribute("href", fullLink);
    });
  }
}());
