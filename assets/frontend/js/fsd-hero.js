(function(){
function px(value){
  var n=parseFloat(value||'0');
  return isNaN(n)?0:n;
}
function measureNodeHeight(node){
  if(!node||node===document.body) return 0;
  var rect=node.getBoundingClientRect?node.getBoundingClientRect():null;
  var h=rect?rect.height:0;
  if(!h&&node.offsetHeight) h=node.offsetHeight;
  return Math.round(h||0);
}
function isElementorNode(node){
  if(!node||!node.classList) return false;
  var cls=(node.className||'').toString();
  return node.classList.contains('e-con')||
    node.classList.contains('elementor-element')||
    node.classList.contains('elementor-widget-shortcode')||
    node.classList.contains('elementor-shortcode')||
    cls.indexOf('elementor')!==-1;
}
function findHeightTarget(root){
  var current=root.parentElement;
  var best=null;
  var bestScore=-1;
  var rootHeight=measureNodeHeight(root);
  while(current&&current!==document.body){
    if(isElementorNode(current)){
      var height=measureNodeHeight(current);
      if(height>0){
        var style=window.getComputedStyle(current);
        var minHeight=px(style.minHeight);
        var score=0;
        if(current.classList.contains('e-con')) score+=300;
        if(current.classList.contains('elementor-top-section')) score+=260;
        if(current.classList.contains('elementor-section')) score+=220;
        if(current.classList.contains('elementor-widget-shortcode')) score-=120;
        if(height>rootHeight+24) score+=180;
        if(minHeight>0) score+=140;
        score+=Math.min(height,2000)/10;
        if(score>bestScore){
          bestScore=score;
          best={node:current,height:height};
        }
      }
    }
    current=current.parentElement;
  }
  return best;
}
function applyHeroHeight(root,height){
  var value=height>0?height+'px':'';
  [root].concat(Array.prototype.slice.call(root.querySelectorAll('.fsd-hero__slides, .fsd-hero__slide, .fsd-hero__overlay, .fsd-hero__align'))).forEach(function(node){
    if(!node) return;
    if(value){
      node.style.height=value;
      node.style.minHeight=value;
    }else{
      node.style.removeProperty('height');
      node.style.removeProperty('min-height');
    }
  });
  if(value){
    root.style.setProperty('--fsd-hero-height',value);
  }else{
    root.style.removeProperty('--fsd-hero-height');
  }
}
function syncHeroHeight(root){
  if(!root) return;
  var target=findHeightTarget(root);
  if(target&&target.height>0){
    applyHeroHeight(root,target.height);
  }else{
    applyHeroHeight(root,0);
  }
}
function initHero(root){
  if(!root||root.dataset.fsdReady==='1') return;
  var slides=root.querySelectorAll('.fsd-hero__slide');
  var dots=root.querySelectorAll('.fsd-hero__dot');
  if(!slides.length) return;
  var index=0; var timer=null;
  var autoplay=slides[0].getAttribute('data-autoplay')==='1';
  var delay=parseInt(slides[0].getAttribute('data-delay')||'5000',10);
  function show(next){
    slides.forEach(function(slide,i){slide.classList.toggle('is-active',i===next);});
    dots.forEach(function(dot,i){dot.classList.toggle('is-active',i===next);});
    index=next;
    syncHeroHeight(root);
  }
  function start(){if(slides.length<=1||!autoplay)return; stop(); timer=window.setInterval(function(){show((index+1)%slides.length);},delay);}
  function stop(){if(timer){window.clearInterval(timer);timer=null;}}
  dots.forEach(function(dot){dot.addEventListener('click',function(){var target=parseInt(dot.getAttribute('data-target'),10); if(!isNaN(target)){show(target);start();}});});
  root.addEventListener('mouseenter',stop); root.addEventListener('mouseleave',start);
  show(0); start();
  if(window.ResizeObserver){
    var resizeObserver=new ResizeObserver(function(){syncHeroHeight(root);});
    var node=root.parentElement;
    while(node&&node!==document.body){
      resizeObserver.observe(node);
      node=node.parentElement;
    }
    root._fsdResizeObserver=resizeObserver;
  }
  window.addEventListener('load',function(){syncHeroHeight(root);},{once:true});
  window.addEventListener('resize',function(){syncHeroHeight(root);});
  root.dataset.fsdReady='1';
}
function scan(context){var scope=context||document; scope.querySelectorAll('[data-fsd-hero]').forEach(function(root){initHero(root); syncHeroHeight(root);});}
document.addEventListener('DOMContentLoaded',function(){scan(document);});
if(window.elementorFrontend&&window.elementorFrontend.hooks){window.elementorFrontend.hooks.addAction('frontend/element_ready/global',function($scope){if($scope&&$scope[0])scan($scope[0]);});}
var observer=new MutationObserver(function(mutations){mutations.forEach(function(m){m.addedNodes.forEach(function(node){if(node.nodeType===1){if(node.matches&&node.matches('[data-fsd-hero]')){initHero(node);syncHeroHeight(node);}else if(node.querySelectorAll)scan(node);}});});});
observer.observe(document.documentElement,{childList:true,subtree:true});
})();
