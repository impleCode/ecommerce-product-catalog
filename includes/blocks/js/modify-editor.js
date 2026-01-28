/**
 * Modify Gutenberg Editor
 *
 */

// Remove the default "Post Excerpt" panel from the block editor.
wp.data.dispatch('core/edit-post')
    .removeEditorPanel('post-excerpt');
wp.data.dispatch('core/edit-post')
    .removeEditorPanel('featured-image');

