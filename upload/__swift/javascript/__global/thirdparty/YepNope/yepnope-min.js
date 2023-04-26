/**
 * yepnope.js preload prefix
 *
 * by Alex Sexton
 *
 * Use the prefix! modifier to cache content but not execute it
 */
yepnope.addPrefix( 'preload', function ( resource ) {
  resource.noexec = true;
  return resource;
});

/**
 * Yepnope CSS Force prefix
 *
 * Use a combination of any prefix, and they should work
 * Usage: ['css!genStyles.php?234', 'normal-styles.css' ]
 *
 * Official Yepnope Plugin
 *
 * WTFPL License
 *
 * by Alex Sexton | AlexSexton@gmail.com
 */
( function ( yepnope ) {
  // add each prefix
  yepnope.addPrefix( 'css', function ( resource ) {
    // Set the force flag
    resource.forceCSS = true;
    //carry on
    return resource;
  } );
} )( this.yepnope );
