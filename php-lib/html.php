<?php
/*
  +----------------------------------------------------------------------+
  | XHP                                                                  |
  +----------------------------------------------------------------------+
  | Copyright (c) 2009 - 2014 Facebook, Inc. (http://www.facebook.com)   |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE.PHP, and is    |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
*/

/**
 * This is the base library of HTML elements for use in XHP. This includes all
 * non-deprecated tags and attributes. Elements in this file should stay as
 * close to spec as possible. Facebook-specific extensions should go into their
 * own elements.
 */
abstract class :xhp:html-element extends :x:primitive {

  attribute
    // Global HTML attributes
    string accesskey,
    string class,
    bool contenteditable,
    string contextmenu,
    string dir,
    bool draggable,
    string dropzone,
    bool hidden,
    string id,
    bool inert,
    string itemid,
    string itemprop,
    string itemref,
    string itemscope,
    string itemtype,
    string lang,
    string role,
    enum {'true', 'false'} spellcheck,
    string style,
    string tabindex,
    string title,
    enum {'yes', 'no'} translate,

    // Javascript events
    string onabort,
    string onblur,
    string oncancel,
    string oncanplay,
    string oncanplaythrough,
    string onchange,
    string onclick,
    string onclose,
    string oncontextmenu,
    string oncuechange,
    string ondblclick,
    string ondrag,
    string ondragend,
    string ondragenter,
    string ondragexit,
    string ondragleave,
    string ondragover,
    string ondragstart,
    string ondrop,
    string ondurationchange,
    string onemptied,
    string onended,
    string onerror,
    string onfocus,
    string oninput,
    string oninvalid,
    string onkeydown,
    string onkeypress,
    string onkeyup,
    string onload,
    string onloadeddata,
    string onloadedmetadata,
    string onloadstart,
    string onmousedown,
    string onmouseenter,
    string onmouseleave,
    string onmousemove,
    string onmouseout,
    string onmouseover,
    string onmouseup,
    string onmousewheel,
    string onpause,
    string onplay,
    string onplaying,
    string onprogress,
    string onratechange,
    string onreset,
    string onresize,
    string onscroll,
    string onseeked,
    string onseeking,
    string onselect,
    string onshow,
    string onstalled,
    string onsubmit,
    string onsuspend,
    string ontimeupdate,
    string ontoggle,
    string onvolumechange,
    string onwaiting;

  protected $tagName;

  public function getID() {
    return $this->requireUniqueID();
  }

  public function requireUniqueID() {
    if (!($id = $this->getAttribute('id'))) {
      $this->setAttribute('id', $id = substr(md5(mt_rand(0, 100000)), 0, 10));
    }
    return $id;
  }

  protected final function renderBaseAttrs() {
    $buf = '<'.$this->tagName;
    foreach ($this->getAttributes() as $key => $val) {
      if ($val !== null && $val !== false) {
        if ($val === true) {
          $buf .= ' '.htmlspecialchars($key);
        } else {
          $buf .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($val, ENT_COMPAT).'"';
        }
      }
    }
    return $buf;
  }

  public function addClass($class) {
    $this->setAttribute('class', trim($this->getAttribute('class').' '.$class));
    return $this;
  }

  public function conditionClass($cond, $class) {
    if ($cond) {
      $this->addClass($class);
    }
    return $this;
  }

  protected function stringify() {
    $buf = $this->renderBaseAttrs().'>';
    foreach ($this->getChildren() as $child) {
      $buf .= :xhp::renderChild($child);
    }
    $buf .= '</'.$this->tagName.'>';
    return $buf;
  }
}

/**
 * Subclasses of :xhp:html-singleton may not contain children. When
 * rendered they will be in singleton (<img />, <br />) form.
 */
abstract class :xhp:html-singleton extends :xhp:html-element {
  children empty;

  protected function stringify() {
    return $this->renderBaseAttrs().'>';
  }
}

/**
 * Subclasses of :xhp:pcdata-elements may contain only string children.
 */
abstract class :xhp:pcdata-element extends :xhp:html-element {
  children (pcdata)*;
}

/**
 * Subclasses of :xhp:raw-pcdata-element must contain only string children.
 * However, the strings will not be escaped. This is intended for tags like
 * <script> or <style> whose content is interpreted literally by the browser.
 *
 * From section 6.2 of the HTML 4.01 spec: "Although the STYLE and SCRIPT
 * elements use CDATA for their data model, for these elements, CDATA must be
 * handled differently by user agents. Markup and entities must be treated as
 * raw text and passed to the application as is. The first occurrence of the
 * character sequence "</" (end-tag open delimiter) is treated as terminating
 * the end of the s content. In valid documents, this would be the end tag for
 * the element."
 */
abstract class :xhp:raw-pcdata-element extends :xhp:pcdata-element {
  protected function stringify() {
    $buf = $this->renderBaseAttrs() . '>';
    foreach ($this->getChildren() as $child) {
      if (!is_string($child)) {
        throw new XHPClassException($this, 'Child must be a string');
      }
      $buf .= $child;
    }
    $buf .= '</'.$this->tagName.'>';
    return $buf;
  }
}

/**
 * Below is a big wall of element definitions. These are the basic building
 * blocks of XHP pages.
 */
class :a extends :xhp:html-element {
  attribute
    string download, string href, string hreflang, string media, string rel,
    string target, string type,
    // Legacy
    string name;
  category %flow, %phrase, %interactive;
  // Should not contain %interactive
  children (pcdata | %flow)*;
  protected $tagName = 'a';
}

class :abbr extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'abbr';
}

class :address extends :xhp:html-element {
  category %flow;
  // May not contain %heading, %sectioning, :header, :footer, or :address
  children (pcdata | %flow)*;
  protected $tagName = 'address';
}

class :area extends :xhp:html-singleton {
  attribute
    string alt, string coords, string download, string href, bool hreflang,
    string media, string rel,
    enum {
      'circ', 'circle', 'default', 'poly', 'polygon', 'rect', 'rectangle'
    } shape, string target, string type;
  category %flow, %phrase;
  protected $tagName = 'area';
}

class :article extends :xhp:html-element {
  category %flow, %sectioning;
  children (pcdata | %flow)*;
  protected $tagName = 'article';
}

class :aside extends :xhp:html-element {
  category %flow, %sectioning;
  children (pcdata | %flow)*;
  protected $tagName = 'aside';
}

class :audio extends :xhp:html-element {
  attribute
    bool autoplay, bool controls,
    enum {'anonymous', 'use-credentials'} crossorigin, bool loop,
    string mediagroup, bool muted, enum {'none', 'metadata', 'auto'} preload,
    string src;
  category %flow, %phrase, %embedded, %interactive;
  children (:source*, :track*, (pcdata | %flow)*);
  protected $tagName = 'audio';
}

class :b extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'b';
}

class :base extends :xhp:html-singleton {
  attribute string href, string target;
  category %metadata;
  protected $tagName = 'base';
}

class :bdi extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'bdi';
}

class :bdo extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'bdo';
}

class :blockquote extends :xhp:html-element {
  attribute string cite;
  category %flow, %sectioning;
  children (pcdata | %flow)*;
  protected $tagName = 'blockquote';
}

class :body extends :xhp:html-element {
  attribute
    string onafterprint, string onbeforeprint, string onbeforeunload,
    string onhashchange, string onmessage, string onoffline, string ononline,
    string onpagehide, string onpageshow, string onpopstate, string onstorage,
    string onunload;
  children (pcdata | %flow)*;
  protected $tagName = 'body';
}

class :br extends :xhp:html-singleton {
  category %flow, %phrase;
  protected $tagName = 'br';
}

class :button extends :xhp:html-element {
  attribute
    bool autofocus, bool disabled, string form, string formaction,
    string formenctype, enum {'get', 'post'} formmethod, bool formnovalidate,
    string formtarget, string menu, string name,
    enum {'submit', 'button', 'reset'} type, string value;
  category %flow, %phrase, %interactive;
  // Should not contain interactive
  children (pcdata | %phrase)*;
  protected $tagName = 'button';
}

class :caption extends :xhp:html-element {
  // Should not contain :table
  children (pcdata | %flow)*;
  protected $tagName = 'caption';
}

class :canvas extends :xhp:html-element {
  attribute int height, int width;
  category %flow, %phrase, %embedded;
  // Should not contain :table
  children (pcdata | %flow)*;
  protected $tagName = 'canvas';
}

class :cite extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'cite';
}

class :code extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'code';
}

class :col extends :xhp:html-singleton {
  attribute int span;
  protected $tagName = 'col';
}

class :colgroup extends :xhp:html-element {
  attribute int span;
  children (:col)*;
  protected $tagName = 'colgroup';
}

class :data extends :xhp:html-element {
  attribute string value @required;
  category %flow, %phrase;
  children (%phrase*);
  protected $tagName = 'data';
}

class :datalist extends :xhp:html-element {
  category %flow, %phrase;
  children (%phrase+ | :option*);
  protected $tagName = 'datalist';
}

class :dd extends :xhp:html-element {
  children (pcdata | %flow)*;
  protected $tagName = 'dd';
}

class :del extends :xhp:html-element {
  attribute string cite, string datetime;
  category %flow, %phrase;
  // transparent
  children (pcdata | %flow)*;
  protected $tagName = 'del';
}

class :details extends :xhp:html-element {
  attribute bool open;
  category %flow, %phrase, %interactive;
  children (:summary, %flow+);
  protected $tagName = 'details';
}

class :dialog extends :xhp:html-element {
  attribute bool open;
  category %flow, %sectioning;
  children (%flow);
  protected $tagName = 'dialog';
}

class :div extends :xhp:html-element {
  category %flow;
  children (pcdata | %flow)*;
  protected $tagName = 'div';
}

class :dfn extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'dfn';
}

class :dl extends :xhp:html-element {
  category %flow;
  children (:dt+, :dd+)*;
  protected $tagName = 'dl';
}

class :dt extends :xhp:html-element {
  children (pcdata | %flow)*;
  protected $tagName = 'dt';
}

class :em extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'em';
}

class :embed extends :xhp:html-element {
  attribute
    int height, string src, string type, int width,
    /**
     * The following attributes are Flash specific.
     * Most notable use: youtube video embedding
     */
    bool allowfullscreen, enum {'always', 'never'} allowscriptaccess,
    string wmode;

  category %flow, %phrase, %embedded, %interactive;
  children (pcdata | %phrase)*;
  protected $tagName = 'embed';
}

class :fieldset extends :xhp:html-element {
  attribute bool disabled, string form, string name;
  category %flow;
  children (:legend?, (pcdata | %flow)*);
  protected $tagName = 'fieldset';
}

class :figcaption extends :xhp:html-element {
  children (pcdata | %flow)*;
  protected $tagName = 'figcaption';
}

class :figure extends :xhp:html-element {
  category %flow, %sectioning;
  children ((:figcaption, %flow+) | (%flow+, :figcaption?));
  protected $tagName = 'figure';
}

class :footer extends :xhp:html-element {
  category %flow;
  children (pcdata | %flow)*;
  protected $tagName = 'footer';
}

class :form extends :xhp:html-element {
  attribute
    string action, string accept-charset, enum {'on', 'off'} autocomplete,
    string enctype, enum {'get', 'post'} method, string name, bool novalidate,
    string target;
  category %flow;
  // Should not contain :form
  children (pcdata | %flow)*;
  protected $tagName = 'form';
}

class :h1 extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'h1';
}

class :h2 extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'h2';
}

class :h3 extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'h3';
}

class :h4 extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'h4';
}

class :h5 extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'h5';
}

class :h6 extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'h6';
}

class :head extends :xhp:html-element {
  children (%metadata*);
  protected $tagName = 'head';
}

class :header extends :xhp:html-element {
  category %flow, %heading;
  children (pcdata | %flow)*;
  protected $tagName = 'header';
}

class :hgroup extends :xhp:html-element {
  category %flow, %heading;
  children (:h1 | :h2 | :h3 | :h4 | :h5 | :h6)+;
  protected $tagName = 'hgroup';
}

class :hr extends :xhp:html-singleton {
  category %flow;
  protected $tagName = 'hr';
}

class :html extends :xhp:html-element {
  attribute string manifest, string xmlns;
  children (:head, :body);
  protected $tagName = 'html';
}

class :i extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'i';
}

class :iframe extends :xhp:pcdata-element {
  attribute
    bool allowfullscreen, string name, int height, string sandbox,
    bool seamless, string src, string srcdoc, int width;
  category %flow, %phrase, %embedded, %interactive;
  protected $tagName = 'iframe';
}

class :img extends :xhp:html-singleton {
  attribute
    string alt, enum {'anonymous', 'use-credentials'} crossorigin, int height,
    bool ismap, string src, string usemap, int width;
  category %flow, %phrase;
  protected $tagName = 'img';
}

class :input extends :xhp:html-singleton {
  attribute
    string accept, string alt, enum {'on', 'off'} autocomplete, bool autofocus,
    bool checked, string dirname, bool disabled, string form,
    string formaction, string formenctype, enum {'get', 'post'} formmethod,
    bool formnovalidate, string formtarget, int height, enum {
      'email', 'full-width-latin', 'kana', 'katakana', 'latin', 'latin-name',
      'latin-prose', 'numeric', 'tel', 'url', 'verbatim'
    } inputmode, string list, float max, int maxlength, float min,
    int minlength, bool multiple, string name, string pattern,
    string placeholder, bool readonly, bool required, int size, string src,
    float step, enum {
      'hidden', 'text', 'search', 'tel', 'url', 'email', 'password',
      'datetime', 'date', 'month', 'week', 'time', 'datetime-local', 'number',
      'range', 'color', 'checkbox', 'radio', 'file', 'submit', 'image',
      'reset', 'button'
    } type, string value, int width;
  category %flow, %phrase, %interactive;
  protected $tagName = 'input';
}

class :ins extends :xhp:html-element {
  attribute string cite, string datetime;
  category %flow, %phrase;
  children (pcdata | %flow)*;
  protected $tagName = 'ins';
}

class :keygen extends :xhp:html-singleton {
  attribute
    bool autofocus, string challenge, bool disabled, string form,
    string keytype, string name;
  category %flow, %phrase, %interactive;
  protected $tagName = 'keygen';
}

class :kbd extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'kbd';
}

class :label extends :xhp:html-element {
  attribute string for, string form;
  category %flow, %phrase, %interactive;
  // may not contain label
  children (pcdata | %phrase)*;
  protected $tagName = 'label';
}

class :legend extends :xhp:html-element {
  children (pcdata | %phrase)*;
  protected $tagName = 'legend';
}

class :li extends :xhp:html-element {
  children (pcdata | %flow)*;
  protected $tagName = 'li';
}

class :link extends :xhp:html-singleton {
  attribute
    enum {'anonymous', 'use-credentials'} crossorigin, string href,
    string hreflang, string media, string rel @required, string sizes,
    string type;
  category %metadata;
  protected $tagName = 'link';
}

class :main extends :xhp:html-element {
  category %flow;
  children (pcdata | %flow)*;
  protected $tagName = 'main';
}

class :map extends :xhp:html-element {
  attribute string name;
  category %flow, %phrase;
  children (pcdata | %flow)*;
  protected $tagName = 'map';
}

class :mark extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'mark';
}

class :menu extends :xhp:html-element {
  attribute string label, enum {'popup', 'toolbar'} type;
  category %flow;
  children ((:menuitem | :hr | :menu)* | :li* | %flow*);
  protected $tagName = 'menu';
}

class :menuitem extends :xhp:html-singleton {
  attribute
    bool checked, string command, bool default, bool disabled,
    string label, string icon, string radiogroup,
    enum {'checkbox', 'command', 'radio'} type;
  protected $tagName = 'menuitem';
}

class :meta extends :xhp:html-singleton {
  attribute
    // The correct definition of http-equiv is an enum, but there are legacy
    // values still used and any strictness here would only be frustrating.
    string charset, string content @required, string http-equiv, string name,
    // Facebook OG
    string property;
  // If itemprop is present, this element is allowed within the <body>.
  category %metadata, %flow, %phrase;
  protected $tagName = 'meta';
}

class :meter extends :xhp:html-element {
  attribute
    float high, float low, float max, float min, float optimum, float value;
  category %flow, %phrase;
  // Should not contain :meter
  children (pcdata | %phrase)*;
  protected $tagName = 'meter';
}

class :nav extends :xhp:html-element {
  category %flow;
  children (pcdata | %flow)*;
  protected $tagName = 'nav';
}

class :noscript extends :xhp:html-element {
  children (pcdata | %metadata | %flow)*;
  category %flow, %phrase, %metadata;
  protected $tagName = 'noscript';
}

class :object extends :xhp:html-element {
  attribute
    string data, int height, string form, string name, string type,
    bool typemustmatch, string usemap, int width;
  category %flow, %phrase, %embedded, %interactive;
  children (:param*, (pcdata | %flow)*);
  protected $tagName = 'object';
}

class :ol extends :xhp:html-element {
  attribute bool reversed, int start, enum {'1', 'a', 'A', 'i', 'I'} type;
  category %flow;
  children (:li)*;
  protected $tagName = 'ol';
}

class :optgroup extends :xhp:html-element {
  attribute bool disabled, string label;
  children (:option)*;
  protected $tagName = 'optgroup';
}

class :option extends :xhp:pcdata-element {
  attribute bool disabled, string label, bool selected, string value;
  protected $tagName = 'option';
}

class :output extends :xhp:html-element {
  attribute string for, string form, string name;
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'output';
}

class :p extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'p';
}

class :param extends :xhp:pcdata-element {
  attribute string name, string value;
  protected $tagName = 'param';
}

class :pre extends :xhp:html-element {
  category %flow;
  children (pcdata | %phrase)*;
  protected $tagName = 'pre';
}

class :progress extends :xhp:html-element {
  attribute float max, float value;
  category %flow, %phrase;
  // Should not contain :progress
  children (pcdata | %phrase)*;
  protected $tagName = 'progress';
}

class :q extends :xhp:html-element {
  attribute string cite;
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'q';
}

class :rb extends :xhp:html-element {
  children (pcdata | %phrase)+;
  protected $tagName = 'rb';
}

class :rp extends :xhp:html-element {
  children (pcdata | %phrase)+;
  protected $tagName = 'rp';
}

class :rt extends :xhp:html-element {
  children (pcdata | %phrase)+;
  protected $tagName = 'rt';
}

class :rtc extends :xhp:html-element {
  children (pcdata | %phrase)+;
  protected $tagName = 'rtc';
}

class :ruby extends :xhp:html-element {
  category %flow, %phrase;
  children (
    (pcdata | :rb)+ |
    ((:rp, :rt) | (:rp, :rtc) | (:rt, :rp) | (:rtc, :rp))+
  );
  protected $tagName = 'ruby';
}

class :s extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 's';
}

class :samp extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'samp';
}

class :script extends :xhp:raw-pcdata-element {
  attribute bool async, string charset,
  enum {'anonymous', 'use-credentials'} crossorigin, bool defer, string src,
  string type,
  // Legacy
  string language;
  category %flow, %phrase, %metadata;
  protected $tagName = 'script';
}

class :section extends :xhp:html-element {
  category %flow, %sectioning;
  children (pcdata | %flow)*;
  protected $tagName = 'section';
}

class :select extends :xhp:html-element {
  attribute
    bool autofocus, bool disabled, string form, bool multiple, string name,
    bool required, int size;
  category %flow, %phrase, %interactive;
  children (:option | :optgroup)*;
  protected $tagName = 'select';
}

class :small extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'small';
}

class :source extends :xhp:html-singleton {
  attribute string media, string src, string type;
  protected $tagName = 'source';
}

class :span extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'span';
}

class :strong extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'strong';
}

class :style extends :xhp:raw-pcdata-element {
  attribute
    string media, bool scoped, string type;
  category %flow, %metadata;
  protected $tagName = 'style';
}

class :sub extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'sub';
}

class :summary extends :xhp:html-element {
  children (pcdata | %phrase)*;
  protected $tagName = 'summary';
}

class :sup extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'sup';
}

class :table extends :xhp:html-element {
  attribute int border, bool sortable;
  category %flow;
  children (
    :caption?,
    :colgroup*,
    :thead?,
    (
      (:tfoot, (:tbody+ | :tr*)) |
      ((:tbody+ | :tr*), :tfoot?)
    )
  );
  protected $tagName = 'table';
}

class :tbody extends :xhp:html-element {
  children (:tr)*;
  protected $tagName = 'tbody';
}

class :template extends :xhp:html-element {
  category %flow, %phrase, %metadata;
  // The children declaration for this element is extraordinarily verbose so
  // I leave it to you to use it appropriately.
  protected $tagName = 'template';
}

class :td extends :xhp:html-element {
  attribute int colspan, string headers, int rowspan;
  children (pcdata | %flow)*;
  protected $tagName = 'td';
}

class :textarea extends :xhp:pcdata-element {
  attribute
    enum {'on', 'off'} autocomplete, bool autofocus, int cols, string dirname,
    bool disabled, string form, int maxlength, int minlength, string name,
    string placeholder, bool readonly, bool required, int rows,
    enum {'soft', 'hard'} wrap;
  category %flow, %phrase, %interactive;
  protected $tagName = 'textarea';
}

class :tfoot extends :xhp:html-element {
  children (:tr)*;
  protected $tagName = 'tfoot';
}

class :th extends :xhp:html-element {
  attribute
    string abbr, int colspan, string headers, int rowspan,
    enum {'col', 'colgroup', 'row', 'rowgroup'} scope, string sorted;
  children (pcdata | %flow)*;
  protected $tagName = 'th';
}

class :thead extends :xhp:html-element {
  children (:tr)*;
  protected $tagName = 'thead';
}

class :time extends :xhp:html-element {
  attribute string datetime;
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'time';
}

class :title extends :xhp:pcdata-element {
  category %metadata;
  protected $tagName = 'title';
}

class :tr extends :xhp:html-element {
  children (:th | :td)*;
  protected $tagName = 'tr';
}

class :track extends :xhp:html-singleton {
  attribute
    bool default,
    enum {
      'subtitles', 'captions', 'descriptions', 'chapters', 'metadata'
    } kind, string label, string src, string srclang;
  protected $tagName = 'track';
}

class :tt extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'tt';
}

class :u extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'u';
}

class :ul extends :xhp:html-element {
  category %flow;
  children (:li)*;
  protected $tagName = 'ul';
}

class :var extends :xhp:html-element {
  category %flow, %phrase;
  children (pcdata | %phrase)*;
  protected $tagName = 'var';
}

class :video extends :xhp:html-element {
  attribute
    bool autoplay, bool controls,
    enum {'anonymous', 'use-credentials'} crossorigin, int height, bool loop,
    string mediagroup, bool muted, string poster,
    enum {'none', 'metadata', 'auto'} preload, string src, int width;
  category %flow, %phrase, %embedded, %interactive;
  children (:source*, :track*, (pcdata | %flow)*);
  protected $tagName = 'video';
}

class :wbr extends :xhp:html-singleton {
  category %flow, %phrase;
  protected $tagName = 'wbr';
}

/**
 * Render an <html /> element with a DOCTYPE, XHP has chosen to only support
 * the HTML5 doctype.
 */
class :x:doctype extends :x:primitive {
  children (:html);

  protected function stringify() {
    $children = $this->getChildren();
    return '<!DOCTYPE html>' . (:xhp::renderChild($children[0]));
  }
}

/**
 * Render an HTML conditional comment. You can choose whatever you like as
 * the conditional statement.
 */
class :x:conditional-comment extends :x:primitive {
  attribute string if @required;
  children (pcdata | :xhp)*;

  protected function stringify() {
    $children = $this->getChildren();
    $html = '<!--[if '.$this->getAttribute('if').']>';
    foreach ($children as $child) {
      if ($child instanceof :xhp) {
        $html .= :xhp::renderChild($child);
      } else {
        $html .= $child;
      }
    }
    $html .= '<![endif]-->';
    return $html;
  }
}
