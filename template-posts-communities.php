<?php
/**
 * Template Name: [Community] artikelen archief
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 0.1
 */
global $paged;
if ( ! isset( $paged ) || ! $paged ) {
	$paged = 1;
}

$timber_post                  = new Timber\Post();

$context                      = Timber::context();
$context['post']              = $timber_post;
$context['modifier']           = 'community-post-archive';
$context['is_unboxed']        = true;
$context['has_intro_overlap'] = false; // We only set to true when we have items
$context['show_author']       = false;
$context['title']             = $timber_post->title;

// Use the '00 - Inleiding' `post_inleiding` field as intro text
// when available. If not, we try and add some generic Community info (below)
$context['descr'] = wpautop( get_field( 'post_inleiding' ) );

$templates        = [ 'community-posts-archive.twig', 'archive.twig' ];

// Get current community based on Parent Page
// If no community found, render a default page
if ( ! empty( $timber_post->post_parent ) ) {
    $parent_page_template = get_post_meta( $timber_post->post_parent, '_wp_page_template', true );
    // Try and retrieve the Community tax. Term from parent page.
    if ( $parent_page_template === GC_COMMUNITY_TAX_DETAIL_TEMPLATE ) {
        $parent_page_community_term_id = get_field( 'community_detail_select_community_term', $timber_post->post_parent ) ?: 0;
        if ( ! empty( $parent_page_community_term_id ) ) {
            $community_term = get_term( $parent_page_community_term_id, GC_COMMUNITY_TAX );
        }
    }
}

// No parent page with Community attached
// See if THIS page has a community attached
if ( ! isset( $community_term ) || ! $community_term instanceof WP_Term ) {
    $page_community_term = wp_get_post_terms( $timber_post->ID, GC_COMMUNITY_TAX );
    if ( ! $page_community_term instanceof WP_Error && ! empty( $page_community_term ) ) {
        $community_term = $page_community_term[0];
    }
}

// At this point we'd expect to have a community term
if ( isset( $community_term ) && ! is_wp_error( $community_term ) ) {
	// Update body class
	$context['body_class'] = ( $context['body_class'] ?: '' ) . ' community--' . $community_term->slug;

    // Check if Archive term has 'palette' or 'visual' fields
    // and add it to context so that we can color header
    // Get custom ACF fields for this WP_Term..
    // filter out 'empty' or nullish values.
    $current_community_term_fields = array_filter(
        get_fields( $community_term ) ?: array(),
        function ( $field ) {
            return ! empty( $field );
        }
    );

    if ( $current_community_term_fields ) {
        // We have some custom ACF fields for this Term

        // If we have a palette:
        if ( isset( $current_community_term_fields['community_taxonomy_colorscheme'] ) ) {
            // .. store it in $context
            $context['palette'] = $current_community_term_fields['community_taxonomy_colorscheme'];
            // .. update body class
            $context['body_class'] = ( $context['body_class'] ?: '' ) . ' palette--' . $context['palette'];
        }

        // If we have a visual, store it in $context
        if ( isset( $current_community_term_fields['community_taxonomy_visual'] ) ) {
            // Story complete Path to image (if available)
            $context['visual'] = $current_community_term_fields['community_taxonomy_visual'];
            if ( defined( 'GC_COMMUNITY_TAX_ASSETS_PATH' ) ) {
                $context['visual'] = sprintf( '%s/images/%s', GC_COMMUNITY_TAX_ASSETS_PATH, $context['visual'] );
            }
        }

        // // If we have an extra Community Link
        // if ( isset( $current_community_term_fields['community_taxonomy_link'] ) ) {
        //     $context['community_link'] = $current_community_term_fields['community_taxonomy_link'];
        // }
    }

    // Fallback: Term VISUAL
    if ( ! array_key_exists( 'visual', $context ) ) {
        $context['visual'] = sprintf( '%s/images/', GC_COMMUNITY_TAX_ASSETS_PATH ) . 'c-default.svg';
    }

    // Fill context with Community Posts
    $context['items'] = array();

    // Determine the post types we need to filter on
    $post_types    = array( 'post' ); // array with all post types to show
    $posts_per_page = get_option( 'posts_per_page' );
    $args         = array(
        'post_type'      => $post_types,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'tax_query'      => array(
            array(
                'taxonomy' => GC_COMMUNITY_TAX,
                'field'    => 'slug',
                'terms'    => $community_term->slug,
            ),
        ),
    );

    $context['posts'] = new Timber\PostQuery( $args );

    if ( count( $context['posts'] ) > 0 ) {
        // Set intro overlap to true, we pull up 1st item into intro
        $context['has_intro_overlap'] = true;

        foreach ( $context['posts'] as $post ) {
            $community_post = prepare_card_content( $post );
            // Hard reset the featured image: we never want it in this archive..
            $community_post['featured_post_image'] = null;
            $context['items'][] = $community_post;
        }
    } else {
        $context['feedbackmessage'] = sprintf( '<p>%s</p>', _x( "Geen berichten gevonden.", 'LLK no content found', 'gctheme' ) );
    }

    // `post_inleiding` field is empty.
    // Update the intro with some community details
    // If it is not yet filled with `post_inleiding` field
    if ( empty( $context['descr'] ) ) {
        // Fallback for when we can not link to community page
        $community_name = sprintf( '<i>%s</i>', $community_term->name );
        // Do we have a community page ID? Link community name instead.
        $community_page_id = $current_community_term_fields['community_taxonomy_page'];
        if ( ! empty( $community_page_id ) ) {
            $community_name = sprintf(
                '<a href="%s">%s</a>',
                get_permalink( $community_page_id ),
                $community_term->name
            );
        }
        $context['descr'] = sprintf(
            '<p>%s</p>',
            $community_term->description ?: sprintf(
                _x( 'Hier vind je artikelen uit de community %s.', 'LLK community archive intro', 'gctheme' ),
                $community_name ?: $community_term->name
            )
        );
    }
} else {
    // No (valid) $community_term found
    // Show a message
    $context['feedbackmessage'] = sprintf( '<p>%s</p>', _x( "Geen berichten gevonden.", 'LLK no content found', 'gctheme' ) );
    // Extra message for editors
    if ( is_user_logged_in() ) {
        $context['feedbackmessage'] .= sprintf( '<p style="color:red">%s</p>', _x( "Er kon geen Community Term worden gevonden. Valt deze pagina wel onder een Community? Zo niet: koppel dan handmatig een Community Term aan deze pagina.", 'LLK no content found, editor message', 'gctheme' ) );
    }
}

Timber::render( $templates, $context );