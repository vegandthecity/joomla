jQuery(function($) {
	// setup getter setter for content id 
	// whoever invented JavaScript obviously wanted to inflict pain on computer scientists...
    var contentIdValue;

    jQuery.contentIdPlugin = {

        contentIdValue: function(value) {

            if (typeof(value) != "undefined") {
                contentIdValue = value;
				return contentIdValue;
            } else {
                return contentIdValue;
            }

        }
	}
	
	// Debug flag
	var debugMode = false;

	// GTM or Universal Analytics
	var gtmMode = true;

    // Default time delay before checking location
    var callBackTime = 100;

    // # px before tracking a reader
    var readerLocation = 150;
	
	// Time used to determine a reader or scanner reader type
	var readerTime = 60;

    // Set some flags for tracking & execution
    var timer = 0;
    var scroller = false;
    var endContent = false;
    var didComplete = false;

    // Set some time variables to calculate reading time
    var startTime = new Date();
    var beginning = startTime.getTime();
    var totalTime = 0;
    
    // Get some information about the current page
    var pageTitle = document.title;

    // Track the aticle load
    if (!debugMode) {
				if (gtmMode) {
					dataLayer.push({
						'event':'content-tracker',
						'content-category':'Reading',
						'content-action':'ArticleLoaded',
						'content-label':'',
						'content-value':'',
						'content-non-interaction':true
						});
				} else {
					ga('send','event', 'Reading', 'ArticleLoaded', '', '', true);
				}
    } else {
        alert('The page has loaded. Woohoo.');    
    }

    // Check the location and track user
    function trackLocation() {
        bottom = $(window).height() + $(window).scrollTop();
        height = $(document).height();

        // If user starts to scroll send an event
        if (bottom > readerLocation && !scroller) {
            currentTime = new Date();
            scrollStart = currentTime.getTime();
            timeToScroll = Math.round((scrollStart - beginning) / 1000);
            if (!debugMode) {
				if (gtmMode) {
					dataLayer.push({
						'event':'content-tracker',
						'content-category':'Reading',
						'content-action':'StartReading',
						'content-label':'',
						'content-value':timeToScroll,
						'content-non-interaction':false
						});
				} else {
					ga('send','event', 'Reading', 'StartReading', '', timeToScroll);
				}
			} else {
                alert('started reading ' + timeToScroll);
            }
            scroller = true;
        }

        // If user has hit the bottom of the content send an event
        if (bottom >= jQuery('.'+jQuery.contentIdPlugin.contentIdValue()).scrollTop() + jQuery('.'+jQuery.contentIdPlugin.contentIdValue()).innerHeight() && !endContent) {
            currentTime = new Date();
            contentScrollEnd = currentTime.getTime();
            timeToContentEnd = Math.round((contentScrollEnd - scrollStart) / 1000);
            if (!debugMode) {
				if (gtmMode) {
					dataLayer.push({
						'event':'content-tracker',
						'content-category':'Reading',
						'content-action':'ContentBottom',
						'content-label':'',
						'content-value':timeToContentEnd,
						'content-non-interaction':false
						});
				} else {
					ga('send','event', 'Reading', 'ContentBottom', '', timeToContentEnd);
				}
            } else {
                alert('end content section '+timeToContentEnd);
            }
            endContent = true;
        }

        // If user has hit the bottom of page send an event
        if (bottom >= height && !didComplete) {
            currentTime = new Date();
            end = currentTime.getTime();
            totalTime = Math.round((end - scrollStart) / 1000);
            if (!debugMode) {
				if (gtmMode) {
					if (totalTime < readerTime) {
						dataLayer.push({
							'event':'content-tracker',
							'content-category':'Reading',
							'content-action':'PageBottom',
							'content-label':pageTitle,
							'content-value':totalTime,
							'content-non-interaction':false,
							'readerType':'Scanner'
							});
					} else {
						dataLayer.push({
							'event':'content-tracker',
							'content-category':'Reading',
							'content-action':'PageBottom',
							'content-label':pageTitle,
							'content-value':totalTime,
							'content-non-interaction':false,
							'readerType':'Reader'
							});
					}
				} else {
					if (totalTime < readerTime) {
						ga('set','dimension5', 'Scanner');
					} else {
						ga('set','dimension5', 'Reader');
					}
					ga('send','event', 'Reading', 'PageBottom', pageTitle, totalTime);
				}
            } else {
                alert('bottom of page '+totalTime);
            }
            didComplete = true;
        }
    }

    // Track the scrolling and track location
    $(window).scroll(function() {
        if (timer) {
            clearTimeout(timer);
        }

        // Use a buffer so we don't call trackLocation too often.
        timer = setTimeout(trackLocation, callBackTime);
    });
});
