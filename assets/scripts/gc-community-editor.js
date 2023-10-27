/**
 * Community specific editor functions
 */

if ( wp ) {
    wp.domReady( () => {
        // Remove Community Taxonomy panel from sidebar
        // on pages that have the Community Detail Page template
        wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'taxonomy-panel-community' );
    } );
}
