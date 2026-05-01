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

        $('#speechToggle').on('click', function () {
            var text = $('.single-post .entry-content').text().trim();
            if (!text || !('speechSynthesis' in window)) {
                return;
            }

            if (window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                $(this).removeClass('is-speaking');
                return;
            }

            var utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'zh-CN';
            utterance.onend = function () {
                $('#speechToggle').removeClass('is-speaking');
            };
            window.speechSynthesis.speak(utterance);
            $(this).addClass('is-speaking');
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
            var insertText = $(this).data('emoji-insert');
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

        $('#mobileMenuBtn').on('click', function () {
            $('body').toggleClass('mobile-drawer-open');
        });

        $('#mobileSidebarBtn').on('click', function () {
            var el = document.querySelector('.right-sidebar');
            if (el && typeof el.scrollIntoView === 'function') {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
