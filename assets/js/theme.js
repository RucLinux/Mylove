(function ($) {
    'use strict';

    function insertHtmlIntoActiveEditable(html) {
        var active = document.activeElement;
        if (!active) {
            return false;
        }

        if (active.isContentEditable) {
            document.execCommand('insertHTML', false, html);
            return true;
        }

        return false;
    }

    function insertAtCursor($target, html) {
        if (!$target || $target.length === 0) {
            return false;
        }

        var el = $target.get(0);
        if (!el) {
            return false;
        }

        if (el.tagName === 'TEXTAREA' || el.tagName === 'INPUT') {
            var start = el.selectionStart || 0;
            var end = el.selectionEnd || 0;
            var value = $target.val() || '';
            var newValue = value.substring(0, start) + html + value.substring(end);
            $target.val(newValue).focus();
            var cursor = start + html.length;
            if (typeof el.setSelectionRange === 'function') {
                el.setSelectionRange(cursor, cursor);
            }
            return true;
        }

        return false;
    }

    $(function () {
        var fontSizes = [16, 18, 20, 18];
        var fontIndex = 0;

        $('#fontSizeToggle').on('click', function () {
            fontIndex = (fontIndex + 1) % fontSizes.length;
            $('.single-post .entry-content').css('font-size', fontSizes[fontIndex] + 'px');
        });

        $('#readModeToggle').on('click', function () {
            $('body').toggleClass('read-mode-open');
        });

        /**
         * 朗读：华为 / iOS 等 WebView 要求 speechSynthesis.speak() 留在用户点击的**同步栈**内；
         * 若用 setTimeout / rAF 再 speak，会被视为非用户手势而静默失败（点击无反应）。
         * 同一次 click 内：cancel → 极短「唤醒」utterance + cancel → getVoices → 正文 speak（同步）。
         */
        function xghomePickZhVoice(voices) {
            if (!voices || !voices.length) {
                return null;
            }
            var prefer = ['zh-CN', 'zh-cn', 'zh-Hans', 'zh', 'cmn-CN'];
            var i, j, v;
            for (i = 0; i < prefer.length; i++) {
                var p = prefer[i].toLowerCase();
                for (j = 0; j < voices.length; j++) {
                    v = voices[j];
                    if (v.lang && v.lang.toLowerCase().indexOf(p) === 0) {
                        return v;
                    }
                }
            }
            for (j = 0; j < voices.length; j++) {
                v = voices[j];
                if (v.lang && v.lang.toLowerCase().indexOf('zh') !== -1) {
                    return v;
                }
            }
            return voices[0];
        }

        /** 在用户手势同步阶段唤醒 TTS（华为 / iOS 常见需要一次空 speak 再 cancel） */
        function xghomeSpeechUnlockSync() {
            try {
                window.speechSynthesis.cancel();
            } catch (e0) {}
            try {
                var probe = new SpeechSynthesisUtterance('\u200b');
                probe.volume = 0.01;
                probe.rate = 10;
                window.speechSynthesis.speak(probe);
            } catch (e1) {}
            try {
                window.speechSynthesis.cancel();
            } catch (e2) {}
        }

        function xghomeSpeakArticle(text, $btn) {
            if (!text || !('speechSynthesis' in window)) {
                return;
            }
            try {
                if (window.speechSynthesis.paused) {
                    window.speechSynthesis.resume();
                }
            } catch (eR) {}

            xghomeSpeechUnlockSync();

            try {
                window.speechSynthesis.getVoices();
            } catch (eV) {}

            var utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'zh-CN';
            utterance.rate = 1;
            utterance.pitch = 1;
            utterance.volume = 1;
            utterance.onstart = function () {
                $btn.addClass('is-speaking');
            };
            utterance.onend = function () {
                $btn.removeClass('is-speaking');
            };
            utterance.onerror = function () {
                $btn.removeClass('is-speaking');
            };

            var voices = window.speechSynthesis.getVoices();
            var picked = xghomePickZhVoice(voices);
            if (picked) {
                utterance.voice = picked;
            }

            try {
                window.speechSynthesis.speak(utterance);
            } catch (eSpeak) {
                $btn.removeClass('is-speaking');
            }
        }

        $('#speechToggle').on('click', function () {
            var $btn = $(this);
            var text = $('.single-post .entry-content').text().trim();
            if (!text || !('speechSynthesis' in window)) {
                return;
            }

            if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
                try {
                    window.speechSynthesis.cancel();
                } catch (e3) {}
                $btn.removeClass('is-speaking');
                return;
            }

            xghomeSpeakArticle(text, $btn);
        });

        var $codeBlocks = $('.single-post .entry-content pre code');
        if (typeof window.hljs !== 'undefined' && $codeBlocks.length) {
            $codeBlocks.each(function () {
                window.hljs.highlightElement(this);
            });
        }

        $('#scrollToTop').on('click', function () {
            $('html, body').animate({ scrollTop: 0 }, 380);
        });
        $('#scrollToBottom').on('click', function () {
            var target = Math.max($(document).height() - $(window).height(), 0);
            $('html, body').animate({ scrollTop: target }, 380);
        });

        $('.emoji-set-tab').on('click', function () {
            var target = $(this).data('emoji-set');
            $('.emoji-set-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $('.emoji-set-panel').removeClass('is-active');
            $('.emoji-set-panel[data-emoji-panel="' + target + '"]').addClass('is-active');
        });

        $('.emoji-item-btn').on('click', function () {
            var insertText = $(this).attr('data-emoji-insert');
            var $wrap = $(this).closest('.emoji-picker-wrap');
            var targetSelector = $wrap.data('emoji-target') || '#comment';
            var $target = $(targetSelector);

            if (typeof window.tinymce !== 'undefined' && targetSelector === '#content') {
                var editor = window.tinymce.get('content');
                if (editor && !editor.isHidden()) {
                    editor.execCommand('mceInsertContent', false, insertText);
                    return;
                }
            }

            if (insertAtCursor($target, insertText)) {
                return;
            }

            insertHtmlIntoActiveEditable(insertText);
        });

        function xghomeCloseMobileSearch() {
            $('body').removeClass('mobile-search-open');
            $('#mobileSearchPanel').attr('hidden', 'hidden');
            $('#mobileSearchBtn').attr('aria-expanded', 'false');
            var $sf = $('#mobileSearchPanel input[type="search"], #mobileSearchPanel input[name="s"]');
            if ($sf.length) {
                $sf.blur();
            }
        }

        function xghomeCloseMobileDrawer() {
            $('body').removeClass('mobile-drawer-open');
            $('#mobileDrawer').attr('aria-hidden', 'true');
            $('#mobileMenuBtn').attr('aria-expanded', 'false');
            $('#mobileDrawerBackdrop').attr('hidden', 'hidden').attr('aria-hidden', 'true');
        }

        $('#mobileMenuBtn').on('click', function () {
            var open = !$('body').hasClass('mobile-drawer-open');
            xghomeCloseMobileSearch();
            $('body').toggleClass('mobile-drawer-open', open);
            $('#mobileDrawer').attr('aria-hidden', open ? 'false' : 'true');
            $(this).attr('aria-expanded', open ? 'true' : 'false');
            if (open) {
                $('#mobileDrawerBackdrop').removeAttr('hidden').attr('aria-hidden', 'false');
            } else {
                $('#mobileDrawerBackdrop').attr('hidden', 'hidden').attr('aria-hidden', 'true');
            }
        });

        $('#mobileDrawerBackdrop').on('click', function () {
            xghomeCloseMobileDrawer();
        });

        $(document).on('click', '#mobileDrawer a[href]', function () {
            xghomeCloseMobileDrawer();
        });

        $('#mobileSearchBtn').on('click', function () {
            var open = !$('body').hasClass('mobile-search-open');
            if (open) {
                xghomeCloseMobileDrawer();
            }
            $('body').toggleClass('mobile-search-open', open);
            var $panel = $('#mobileSearchPanel');
            if (open) {
                $panel.removeAttr('hidden');
            } else {
                $panel.attr('hidden', 'hidden');
            }
            $(this).attr('aria-expanded', open ? 'true' : 'false');
            if (open) {
                setTimeout(function () {
                    var $inp = $('#mobileSearchPanel input[type="search"], #mobileSearchPanel input[name="s"]').first();
                    if ($inp.length) {
                        $inp.trigger('focus');
                    }
                }, 50);
            }
        });

        $('#mobileSearchClose').on('click', function () {
            xghomeCloseMobileSearch();
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                xghomeCloseMobileDrawer();
                xghomeCloseMobileSearch();
            }
        });

        $('.top-action-btn').on('click', function () {
            var target = $(this).data('top-panel');
            var $panel = $('.top-panel[data-top-panel-id="' + target + '"]');
            var shouldOpen = !$panel.hasClass('is-active');

            $('.top-action-btn').removeClass('is-active');
            $('.top-panel').removeClass('is-active');

            if (shouldOpen) {
                $(this).addClass('is-active');
                $panel.addClass('is-active');
            }
        });

        $('.tab-nav a').on('click', function (e) {
            e.preventDefault();
            var target = $(this).data('tab-target');

            $(this).closest('.tab-nav').find('a').removeClass('is-active');
            $(this).addClass('is-active');

            var $root = $(this).closest('.tab-widget');
            $root.find('.tab-panel').removeClass('is-active');
            $root.find('.tab-panel[data-tab-panel="' + target + '"]').addClass('is-active');
        });

        $('.js-menu-toggle').on('click', function () {
            var $btn = $(this);
            var target = $btn.data('target');
            if (!target) {
                return;
            }
            var $target = $(target);
            if (!$target.length) {
                return;
            }
            var willExpand = $target.hasClass('is-collapsed');
            if (willExpand) {
                $('.js-menu-toggle').not($btn).each(function () {
                    var $otherBtn = $(this);
                    var otherTarget = $otherBtn.data('target');
                    if (!otherTarget) {
                        return;
                    }
                    var $otherMenu = $(otherTarget);
                    if ($otherMenu.length) {
                        $otherMenu.addClass('is-collapsed');
                    }
                    $otherBtn.removeClass('is-expanded').attr('aria-expanded', 'false');
                });
            }
            $target.toggleClass('is-collapsed', !willExpand);
            $btn.toggleClass('is-expanded', willExpand);
            $btn.attr('aria-expanded', willExpand ? 'true' : 'false');
        });

        $('.js-single-collapse-toggle').on('click', function () {
            var $btn = $(this);
            var target = $btn.data('target');
            if (!target) {
                return;
            }
            var $target = $(target);
            if (!$target.length) {
                return;
            }
            var willCollapse = !$target.hasClass('is-collapsed');
            $target.toggleClass('is-collapsed', willCollapse);
            $btn.attr('aria-expanded', willCollapse ? 'false' : 'true');
            $btn.text(willCollapse ? '⏬' : '⏫');
        });

        var $albumLb = $('#xghomeAlbumLightbox');
        if ($albumLb.length) {
            var $albumImg = $albumLb.find('.album-lightbox-img');

            function albumLbOpen(src) {
                if (!src) {
                    return;
                }
                $albumImg.attr('src', src).attr('alt', '');
                $albumLb.removeAttr('hidden').attr('aria-hidden', 'false');
                $('body').addClass('album-lightbox-open');
            }

            function albumLbClose() {
                $albumLb.attr('hidden', 'hidden').attr('aria-hidden', 'true');
                $albumImg.removeAttr('src');
                $('body').removeClass('album-lightbox-open');
            }

            $(document).on('click', '.js-album-lightbox-trigger', function () {
                var src = $(this).attr('data-full') || '';
                albumLbOpen(src);
            });

            $albumLb.find('.album-lightbox-close').on('click', function (e) {
                e.stopPropagation();
                albumLbClose();
            });

            $albumLb.on('click', function (e) {
                if ($(e.target).closest('.album-lightbox-close').length) {
                    return;
                }
                if ($(e.target).hasClass('album-lightbox-img')) {
                    return;
                }
                albumLbClose();
            });

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && !$albumLb.attr('hidden')) {
                    albumLbClose();
                }
            });
        }

        $('.js-qrcode-image').each(function () {
            var $img = $(this);
            var raw = $img.attr('data-qrcode-apis') || '[]';
            var apis = [];

            try {
                apis = JSON.parse(raw);
            } catch (e) {
                apis = [];
            }

            if (!Array.isArray(apis) || apis.length === 0) {
                return;
            }

            var tryLoad = function (index) {
                if (index >= apis.length) {
                    $img.attr('alt', '二维码加载失败');
                    return;
                }

                $img.off('error.qrcode').on('error.qrcode', function () {
                    tryLoad(index + 1);
                });
                $img.attr('src', apis[index]);
            };

            tryLoad(0);
        });

        $('.js-comment-validate-form').on('submit', function (e) {
            var $form = $(this);
            var $contactInput = $form.find('input[name="email"], input[name="mail"], input[name="contact"]').first();
            if (!$contactInput.length) {
                return true;
            }
            var emailRegex = $('#xghome_comment_email_regex').val() || '';
            var phoneRegex = $('#xghome_comment_phone_regex').val() || '';
            var contact = ($contactInput.val() || '').toString().trim();
            var emailOk = false;
            var phoneOk = false;

            try { emailOk = emailRegex ? new RegExp(emailRegex).test(contact) : false; } catch (err) { emailOk = false; }
            try { phoneOk = phoneRegex ? new RegExp(phoneRegex).test(contact) : false; } catch (err2) { phoneOk = false; }

            if (contact === '') {
                e.preventDefault();
                alert('请输入邮箱或手机号');
                return false;
            }
            if (!emailOk && !phoneOk) {
                e.preventDefault();
                alert('格式不正确');
                return false;
            }
            return true;
        });

        var wm = window.XGHOME_WATERMARK || {};
        if (wm && (wm.image || wm.text)) {
            $('.entry-content img').each(function () {
                var $img = $(this);
                if ($img.closest('.xghome-watermark-wrap').length) {
                    return;
                }
                if ($img.closest('.emoji-picker-wrap').length) {
                    return;
                }
                var $wrap = $('<span class="xghome-watermark-wrap"></span>');
                $img.wrap($wrap);
                var $holder = $img.parent('.xghome-watermark-wrap');
                if (wm.image) {
                    $holder.append('<img class="xghome-watermark-image" src="' + String(wm.image).replace(/"/g, '&quot;') + '" alt="">');
                }
                if (wm.text) {
                    $holder.append('<span class="xghome-watermark-text">' + String(wm.text) + '</span>');
                }
            });
        }

        var anti = window.XGHOME_ANTI_SCRAPE || {};
        if (anti && anti.enabled) {
            if (anti.disableRightClick) {
                $(document).on('contextmenu', '.entry-content', function (e) {
                    e.preventDefault();
                });
            }

            if (anti.disableShortcuts) {
                $(document).on('keydown', function (e) {
                    var target = e.target || e.srcElement;
                    var tag = target && target.tagName ? target.tagName.toLowerCase() : '';
                    if (tag === 'input' || tag === 'textarea' || (target && target.isContentEditable)) {
                        return;
                    }
                    if (e.ctrlKey || e.metaKey) {
                        var k = (e.key || '').toLowerCase();
                        if (['c', 'u', 's', 'p', 'a'].indexOf(k) !== -1) {
                            e.preventDefault();
                        }
                    }
                    if (e.key === 'F12') {
                        e.preventDefault();
                    }
                });
            }

            if (anti.appendCopy) {
                $(document).on('copy', '.entry-content', function (e) {
                    var sel = window.getSelection ? window.getSelection().toString() : '';
                    if (!sel) {
                        return;
                    }
                    var extra = '\n\n—— 来源：' + (anti.siteName || '') + ' ' + (window.location.href || anti.siteUrl || '');
                    var fullText = sel + extra;
                    if (e.originalEvent && e.originalEvent.clipboardData) {
                        e.preventDefault();
                        e.originalEvent.clipboardData.setData('text/plain', fullText);
                    } else if (window.clipboardData) {
                        e.preventDefault();
                        window.clipboardData.setData('Text', fullText);
                    }
                });
            }
        }
    });
})(jQuery);
