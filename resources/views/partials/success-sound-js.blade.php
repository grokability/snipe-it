{{-- Plays the success sound alongside the confetti animation whenever the
     current user has `enable_sounds = 1` on their profile. Included from
     blade/notifications.blade.php on every session()->get('success') /
     'success-unescaped' branch, so it fires the same way confetti does.

     Browser autoplay policy is the sticky part here:
       - Confetti is canvas rendering, no autoplay restriction, always fires.
       - HTMLAudioElement.play() requires a "user gesture" in most browsers.
         Safari is strictest; Chrome has a Media Engagement Index (MEI) that
         relaxes the rule after a few interactions.
       - A form-submit gesture on the PREVIOUS page does NOT carry across the
         redirect. Immediately calling .play() on the new page is treated
         as no-gesture and blocked in Safari.

     Strategy: try to play immediately. If the browser blocks (rejected
     promise), register a one-shot 'click' listener that plays it on the
     user's next tap. That way the sound plays late but doesn't get
     dropped entirely. --}}
@if (auth()->user() && auth()->user()->enable_sounds == '1')
    <script nonce="{{ csrf_token() }}">
        (function () {
            var src = "{{ url('sounds/success.mp3') }}";

            var attemptPlay = function () {
                try {
                    var audio = new Audio(src);
                    var maybePromise = audio.play();
                    if (maybePromise && typeof maybePromise.then === 'function') {
                        return maybePromise;
                    }
                    return Promise.resolve();
                } catch (e) {
                    return Promise.reject(e);
                }
            };

            attemptPlay().catch(function () {
                // Autoplay blocked (typical in Safari + strict Chrome).
                // Arm a one-shot listener that plays on the next user
                // interaction anywhere on the page.
                var armed = false;
                var playOnce = function () {
                    if (armed) return;
                    armed = true;
                    document.removeEventListener('click', playOnce, true);
                    document.removeEventListener('keydown', playOnce, true);
                    document.removeEventListener('touchstart', playOnce, true);
                    attemptPlay().catch(function () { /* give up silently */ });
                };
                document.addEventListener('click', playOnce, true);
                document.addEventListener('keydown', playOnce, true);
                document.addEventListener('touchstart', playOnce, true);
            });
        })();
    </script>
@endif
