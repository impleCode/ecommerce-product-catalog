/**
 * Modify Gutenberg Editor
 *
 */

// Remove the default "Post Excerpt" panel from the block editor.
wp.data.dispatch('core/editor')
    .removeEditorPanel('post-excerpt');
wp.data.dispatch('core/editor')
    .removeEditorPanel('featured-image');

