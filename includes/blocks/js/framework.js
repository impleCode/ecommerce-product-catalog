/*!
 
 (c) 2022 impleCode - https://implecode.com
 */

function ic_register_block(name, context, transform_from) {
    /* globals ic_blocks_js_global */
    const blocks = window.wp.blocks;
    const editor = window.wp.blockEditor;
    const element = window.wp.element;
    //const data = window.wp.data;
    const components = window.wp.components;
    const serverSideRender = window.wp.serverSideRender;
    const decodeEntities = window.wp.htmlEntities.decodeEntities;
    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var RadioControl = components.RadioControl;
    var LoadingControl = components.Spinner;
    var Notice = components.Notice;
    var ToggleControl = components.ToggleControl;
    var CheckboxControl = components.CheckboxControl;
    var SearchControl = components.SearchControl;
    var ImageControl = editor.MediaPlaceholder;
    var Panel = components.Panel;
    //var Panel = components.Panel;
    var PanelBody = components.PanelBody;
    var PanelRow = components.PanelRow;
    var AlignmentToolbar = editor.AlignmentToolbar;
    var ToolbarButton = components.ToolbarButton;
    var BlockControls = editor.BlockControls;
    var useBlockProps = editor.useBlockProps;
    var useState = element.useState;
    var productOptions = [];
    var subname = name.split('/');
    subname = subname[1];
    var transforms = {};
    if (transform_from !== undefined) {
        transforms.from = [
            {
                type: 'block',
                blocks: ['core/legacy-widget'],
                isMatch: ({idBase, instance}) => {
                    if (instance.raw === undefined) {
                        // Can't transform if raw instance is not shown in REST API.
                        return false;
                    }
                    return idBase === transform_from;
                },
                transform: ({instance}) => {
                    return blocks.createBlock(name,
                        instance.raw
                    );
                }
            }
        ];
    }
    blocks.registerBlockType(name, {
        transforms: transforms,
        usesContext: context,
        edit: function (props) {
            const attributes = props.attributes;
            const supported_fields = blocks.getBlockSupport(name, 'ic_fields');
            // props.attributes.ic_context = ic_blocks_js_global.context;
            //props.setAttributes({ic_context: ic_blocks_js_global.context});
            //const [filteredOptions, setFilteredOptions] = useState(productOptions);
            const [isEditing, setIsEditing] = useState(false);

            function selectTitle(title) {
                props.setAttributes({title: title});
            }

            function selectShortcodeSupport(shortcode_support) {
                props.setAttributes({shortcode_support: shortcode_support});
            }

            function selectDropdown(dropdown) {
                props.setAttributes({dropdown: dropdown});
            }

            function selectCount(count) {
                props.setAttributes({count: count});
            }

            function selectHierarchical(hierarchical) {
                props.setAttributes({hierarchical: hierarchical});
            }

            function selectCategories(categories, category_id, category_name) {
                if (Array.isArray(attributes.selectedCategory)) {
                    if (category_id !== undefined) {
                        var changed = false;
                        var selectedCategory = ic_get_block_selected_categories(attributes);
                        selectedCategory = selectedCategory.filter(function (e) {
                            if (typeof e === 'object') {
                                if (e.value === category_id) {
                                    changed = true;
                                }
                                return e.value !== category_id;
                            } else {
                                if (e === category_id) {
                                    changed = true;
                                }
                                return e !== category_id;
                            }
                        });
                        if (categories) {
                            changed = true;
                            //selectedProduct.push(parseInt(product_id, 10));
                            selectedCategory.push({value: category_id, label: category_name});
                        }
                        if (changed) {
                            props.setAttributes({selectedCategory: selectedCategory});
                        }
                        if (attributes.category.length) {
                            props.setAttributes({category: []});
                        }
                        if (selectedCategory.length && attributes.selectedProduct !== undefined && attributes.selectedProduct.length) {
                            props.setAttributes({selectedProduct: []});
                        }
                    } else {
                        props.setAttributes({category: categories});
                    }
                } else {
                    props.setAttributes({category: categories});
                }
            }

            function selectOrderby(orderby) {
                props.setAttributes({orderby: orderby});
            }

            function selectOrder(order) {
                props.setAttributes({order: order});
            }

            function selectTemplate(template) {
                props.setAttributes({archive_template: template});
            }

            function selectPerrow(per_row) {
                props.setAttributes({per_row: per_row});
            }

            function selectLimit(products_limit) {
                props.setAttributes({products_limit: products_limit});
            }

            function selectNoAddCart(NoAddCart) {
                props.setAttributes({no_add_to_cart: NoAddCart});
            }

            function selectProduct(product, product_id, product_name) {
                if (Array.isArray(attributes.selectedProduct)) {
                    if (product_id !== undefined) {
                        var changed = false;
                        var selectedProduct = ic_get_block_selected_products(attributes);
                        selectedProduct = selectedProduct.filter(function (e) {
                            if (typeof e === 'object') {
                                if (e.value === product_id) {
                                    changed = true;
                                }
                                return e.value !== product_id;
                            } else {
                                if (e === product_id) {
                                    changed = true;
                                }
                                return e !== product_id;
                            }
                        });
                        if (product) {
                            changed = true;
                            //selectedProduct.push(parseInt(product_id, 10));
                            selectedProduct.push({value: product_id, label: product_name});
                        }
                        if (changed) {
                            props.setAttributes({selectedProduct: selectedProduct});
                        }
                        if (attributes.product !== undefined && attributes.product.length) {
                            props.setAttributes({product: []});
                        }
                        if (selectedProduct.length && attributes.selectedCategory !== undefined && attributes.selectedCategory.length) {
                            props.setAttributes({selectedCategory: []});
                        }
                    }
                } else {
                    props.setAttributes({selectedProduct: parseInt(product, 10)});
                    if (isEditing) {
                        setIsEditing(false);
                    }
                }
            }

            function onChangeAlignment(newAlignment) {
                props.setAttributes({
                    alignment:
                        newAlignment === undefined ? 'none' : newAlignment
                });
            }

            function onChangeProductSearch(Search) {
                props.setAttributes({
                    productSearch:
                        Search === undefined ? '' : Search
                });
            }

            function onChangeCategorySearch(Search) {
                props.setAttributes({
                    categorySearch:
                        Search === undefined ? '' : Search
                });
            }

            function onChangeProductFromContext(enabled) {
                props.setAttributes({productFromContext: enabled});
                if (enabled) {
                    if (attributes.product !== undefined && attributes.product.length) {
                        props.setAttributes({product: []});
                    }
                    if (attributes.category !== undefined && attributes.category.length) {
                        props.setAttributes({product: []});
                    }
                    if (attributes.selectedCategory !== undefined && attributes.selectedCategory.length) {
                        props.setAttributes({selectedCategory: []});
                    }
                    if (attributes.selectedProduct !== undefined) {
                        if (Array.isArray(attributes.selectedProduct) && attributes.selectedProduct.length) {
                            props.setAttributes({selectedProduct: []});
                        } else {
                            props.setAttributes({selectedProduct: 0});
                        }
                    }
                    if (attributes.categorySearch !== undefined && attributes.categorySearch.length) {
                        props.setAttributes({categorySearch: ''});
                    }
                    if (attributes.productSearch !== undefined && attributes.productSearch.length) {
                        props.setAttributes({productSearch: ''});
                    }
                }
            }

            function onChangeSupported(name, value) {
                props.setAttributes({[name]: value});
            }

            function onChangeImageID(media) {
                props.setAttributes({image_id: media.id ? media.id : 0});
                props.setAttributes({image_id_url: media.url ? media.url : ''});
            }

            var toolbar = [];
            var sidebar = [];
            var sidebar_additional = [];
            var style = {};
            var settings_open = false;
            var settings_content = [];
            var block_data = ic_get_block_data(attributes);
            props.attributes.ic_context_id = ic_blocks_js_global.context.id;
            props.attributes.ic_context_post_type = ic_blocks_js_global.context.type;
            var server_render = el(serverSideRender, {
                key: subname + '-server-side-renderer',
                block: name,
                attributes: props.attributes
            });
            var output = server_render;
            // var post_data = data.select('core').getEntityRecord('postType', props.context.postType, props.context.postId);
            //  var price = post_data.meta._price;
            if (attributes.title !== undefined) {
                var title = attributes.title;
                sidebar.push(el(TextControl, {
                    label: ic_blocks_js_global.strings.select_title,
                    value: title,
                    key: subname + '-title',
                    type: 'text',
                    onChange: selectTitle
                }));
            }
            if (attributes.shortcode_support !== undefined) {
                var shortcode_support = attributes.shortcode_support;
                sidebar.push(el(CheckboxControl, {
                    label: ic_blocks_js_global.strings.select_shortcode_support,
                    value: '1',
                    key: subname + '-shortcode-support',
                    checked: shortcode_support,
                    type: 'checkbox',
                    onChange: selectShortcodeSupport
                }));
            }
            if (attributes.dropdown !== undefined) {
                var dropdown = false;
                if (attributes.dropdown) {
                    dropdown = true;
                }
                sidebar.push(el(CheckboxControl, {
                        label: ic_blocks_js_global.strings.select_dropdown,
                        value: '1',
                        key: subname + '-dropdown',
                        checked: dropdown,
                        type: 'checkbox',
                        onChange: selectDropdown
                    })
                );
            }
            if (attributes.count !== undefined) {
                var count = false;
                if (attributes.count) {
                    count = true;
                }
                sidebar.push(
                    el(CheckboxControl, {
                        label: ic_blocks_js_global.strings.select_count,
                        value: '1',
                        key: subname + '-count',
                        checked: count,
                        type: 'checkbox',
                        onChange: selectCount
                    })
                );
            }
            if (attributes.hierarchical !== undefined) {
                var hierarchical = false;
                if (attributes.hierarchical) {
                    hierarchical = true;
                }
                sidebar.push(el(CheckboxControl, {
                        label: ic_blocks_js_global.strings.select_hierarchical,
                        value: '1',
                        key: subname + '-hierarchical',
                        checked: hierarchical,
                        type: 'checkbox',
                        onChange: selectHierarchical
                    })
                );
            }
            if (attributes.category !== undefined || attributes.selectedCategory !== undefined) {
                var selected_categories = ic_get_block_selected_categories(attributes);
                var categoryOptions = [];
                var categories = block_data.categories;
                var selected_categories_query = block_data.selected_categories;
                var selectedCategoryIDs = [];
                if (categories) {
                    //categoryOptions = [{'value': 0, 'label': ic_blocks_js_global.strings.all}];
                    if (selected_categories_query) {
                        selected_categories_query.forEach((cat) => {
                            if (cat.value !== undefined) {
                                selectedCategoryIDs.push(parseInt(cat.value, 10));
                                if (cat.value !== 0) {
                                    categoryOptions.push({
                                        value: parseInt(cat.value, 10),
                                        label: decodeEntities(cat.label)
                                    });
                                }
                            } else {
                                selectedCategoryIDs.push(parseInt(cat.id, 10));
                                if (cat.id !== 0) {
                                    categoryOptions.push({
                                        value: parseInt(cat.id, 10),
                                        label: decodeEntities(cat.name) + ' (' + cat.count + ')'
                                    });
                                }
                            }

                        });
                    }
                    categories.forEach((cat) => {
                        if (!selectedCategoryIDs.includes(parseInt(cat.id, 10))) {
                            categoryOptions.push({
                                value: parseInt(cat.id, 10),
                                label: decodeEntities(cat.name) + ' (' + cat.count + ')'
                            });
                        }
                    });
                }
                var category_search_panel_body = '';
                var category_selector_search = [];
                var category_selector_categories = [];
                var categorySearch = '';
                if (attributes.categorySearch !== undefined) {
                    categorySearch = attributes.categorySearch;
                    category_selector_search.push(el(SearchControl, {
                        label: ic_blocks_js_global.strings.search_category,
                        value: categorySearch,
                        type: 'string',
                        key: subname + '-search-category',
                        placeholder: ic_blocks_js_global.strings.category_search_placeholder,
                        onChange: onChangeCategorySearch
                    }));
                    categoryOptions.forEach((category) => {
                        category_selector_categories.push(
                            el(PanelRow, {key: subname + '-checkbox-row-category-' + category.value},
                                el(CheckboxControl, {
                                    label: category.label,
                                    checked: selectedCategoryIDs.includes(category.value),
                                    value: category.value,
                                    key: subname + '-checkbox-category-' + category.value,
                                    onChange: function (selected) {
                                        selectCategories(selected, category.value, category.label);
                                    }
                                }))
                        );
                    });
                } else {
                    category_selector_categories.push(el(PanelRow, {
                            key: subname + '-category-select-panel-row'
                        },
                        el(SelectControl, {
                            multiple: 'multiple',
                            label: '',
                            key: subname + '-category-select',
                            value: selected_categories,
                            options: categoryOptions,
                            onChange: selectCategories
                        })
                    ));
                }
                if (categoryOptions.length === 0) {
                    if (block_data.ready) {
                        if (categorySearch) {
                            category_selector_search.push(
                                el(Notice, {
                                    key: subname + 'product-selector-not-found',
                                    status: 'error',
                                    isDismissible: false
                                }, 'Nothing found. Please use a different keyword.')
                            );
                        }
                    } else {
                        category_selector_categories.push(
                            el(LoadingControl, {
                                key: subname + 'category-selector-loading',

                            })
                        );
                    }

                }

                var category_select_panel_body = el(PanelBody, {
                        title: ic_blocks_js_global.strings.select_categories,
                        key: subname + '-category-select-panel-body',
                        className: 'ic-panel-body',
                        initialOpen: selected_categories
                    },
                    category_selector_categories
                );
                if (category_selector_categories.length === 0) {
                    category_select_panel_body = '';
                }
                sidebar_additional.push(
                    category_select_panel_body
                );
                var categories_label = ic_blocks_js_global.strings.choose_categories;
                if (attributes.selectedProduct !== undefined) {
                    categories_label = ic_blocks_js_global.strings.by_category;
                }
                var category_search_panel_body_classname = 'ic-panel-body';
                if (!categorySearch && (categoryOptions.length < 5 || categoryOptions.length === 0)) {
                    category_search_panel_body_classname = 'ic-hidden-panel-body';
                }
                if (category_selector_search.length) {
                    category_search_panel_body = el(PanelBody, {
                            title: ic_blocks_js_global.strings.search_category,
                            key: subname + '-search-category-panel-body',
                            className: category_search_panel_body_classname,
                            initialOpen: true
                        },
                        category_selector_search
                    );
                }
                var category_select_panel = el(Panel, {
                        header: categories_label,
                        key: subname + '-main-panel'
                    },
                    category_search_panel_body,
                    category_select_panel_body
                );
                if (selected_categories.length === 0) {
                    if (settings_content.length === 0) {
                        settings_open = true;
                    }
                } else {
                    settings_open = false;
                }
                settings_content.push(category_select_panel);
            }
            if (attributes.orderby !== undefined) {
                var orderby = attributes.orderby;
                var orderbyOptions = ic_blocks_js_global.category_orderby_options;
                sidebar.push(
                    el(SelectControl, {
                        label: ic_blocks_js_global.strings.select_orderby,
                        value: orderby,
                        key: subname + '-select-orderby',
                        type: 'dropdown',
                        options: orderbyOptions,
                        onChange: selectOrderby
                    })
                );
            }
            if (attributes.order !== undefined) {
                var order = attributes.order;
                var orderOptions = ic_blocks_js_global.order_options;
                sidebar.push(
                    el(SelectControl, {
                        label: ic_blocks_js_global.strings.select_order,
                        value: order,
                        key: subname + '-select-order',
                        type: 'dropdown',
                        options: orderOptions,
                        onChange: selectOrder
                    })
                );
            }
            if (attributes.archive_template !== undefined) {
                if (attributes.archive_template === '' && ic_blocks_js_global.archive_template_def !== undefined) {
                    attributes.archive_template = ic_blocks_js_global.archive_template_def;
                }
                var archive_template = attributes.archive_template;
                var templateOptions = ic_blocks_js_global.template_options;
                sidebar.push(
                    el(SelectControl, {
                        label: ic_blocks_js_global.strings.select_template,
                        value: archive_template,
                        key: subname + '-select-template',
                        type: 'dropdown',
                        options: templateOptions,
                        onChange: selectTemplate
                    })
                );
            }
            if (attributes.per_row !== undefined) {
                if (attributes.per_row === '' && ic_blocks_js_global.per_row_def !== undefined) {
                    attributes.per_row = ic_blocks_js_global.per_row_def;
                }
                var per_row = attributes.per_row;
                sidebar.push(
                    el(TextControl, {
                        label: ic_blocks_js_global.strings.select_perrow,
                        value: per_row,
                        key: subname + '-select-per-row',
                        type: 'number',
                        onChange: selectPerrow
                    })
                );
            }
            if (attributes.products_limit !== undefined) {
                if (attributes.products_limit === '' && ic_blocks_js_global.products_limit_def !== undefined) {
                    attributes.products_limit = ic_blocks_js_global.products_limit_def;
                }
                var products_limit = attributes.products_limit;
                sidebar.push(
                    el(TextControl, {
                        label: ic_blocks_js_global.strings.select_limit,
                        value: products_limit,
                        key: subname + '-select-products-limit',
                        type: 'number',
                        onChange: selectLimit
                    })
                );
            }
            if (attributes.no_add_to_cart !== undefined) {
                var selected_no_add_cart = attributes.no_add_to_cart;
                if (ic_blocks_js_global.price_add_cart_added === '1') {
                    sidebar.push(
                        el(PanelRow, {key: subname + '-panel-body-row-noaddcart'},
                            el(CheckboxControl, {
                                label: ic_blocks_js_global.strings.disable_add_cart,
                                checked: selected_no_add_cart,
                                onChange: selectNoAddCart
                            })
                        )
                    );
                }
            }
            if (supported_fields !== undefined) {
                for (const [param, label] of Object.entries(supported_fields)) {
                    if (attributes[param] !== undefined) {
                        var type = typeof attributes[param];
                        if (type === 'boolean') {
                            sidebar.push(
                                el(PanelRow, {key: subname + '-panel-body-row-' + param},
                                    el(CheckboxControl, {
                                        label: label,
                                        checked: attributes[param],
                                        onChange: function (selected) {
                                            onChangeSupported(param, selected);
                                        }
                                    })
                                )
                            );
                        } else if (type === 'string') {
                            sidebar.push(
                                el(TextControl, {
                                    label: label,
                                    value: attributes[param],
                                    key: subname + '-' + param,
                                    type: 'text',
                                    onChange: function (selected) {
                                        onChangeSupported(param, selected);
                                    }
                                })
                            );
                        }
                    }


                }
                ;
            }
            if (attributes.alignment !== undefined) {
                var alignment = attributes.alignment;
                toolbar.push(
                    el(AlignmentToolbar, {
                        value: alignment,
                        key: subname + '-alignment',
                        onChange: onChangeAlignment
                    })
                );
                style.textAlign = alignment;
            }
            if (attributes.productSearch !== undefined && attributes.selectedProduct !== undefined) {
                var productSearch = attributes.productSearch;
                var selectedProduct = ic_get_block_selected_products(attributes);
                var products = [];
                productOptions = [];
                var selected_products_query = [];
                var selectedProductIDs = [];
                if ((Array.isArray(selectedProduct) && selectedProduct.length !== 0) || (!Array.isArray(selectedProduct) && selectedProduct)) {
                    selected_products_query = block_data.selected_products;
                    if (selected_products_query) {
                        if (Array.isArray(selected_products_query)) {
                            selected_products_query.forEach((prod) => {
                                if (prod.value !== undefined) {
                                    selectedProductIDs.push(parseInt(prod.value, 10));
                                    productOptions.push({
                                        value: parseInt(prod.value, 10),
                                        label: decodeEntities(prod.label)
                                    });
                                } else {
                                    selectedProductIDs.push(parseInt(prod.id, 10));
                                    productOptions.push({
                                        value: parseInt(prod.id, 10),
                                        label: decodeEntities(prod.title.rendered)
                                    });
                                }
                            });
                        } else {
                            selectedProductIDs.push(parseInt(selected_products_query, 10));
                            productOptions.push({value: parseInt(selected_products_query, 10), label: 'Loading...'});
                        }
                    }
                }
                products = block_data.products;
                if (products) {
                    products.forEach((prod) => {
                        if (!selectedProductIDs.includes(parseInt(prod.id, 10))) {
                            productOptions.push({
                                value: parseInt(prod.id, 10),
                                label: decodeEntities(prod.title.rendered)
                            });
                        }
                    });
                }

                var product_selector_search = [
                    el(PanelRow, {key: subname + '-panel-body-row-product-search'},
                        el(SearchControl, {
                            label: ic_blocks_js_global.strings.search_product,
                            value: productSearch,
                            type: 'string',
                            key: subname + '-search-product',
                            className: 'ic-search-control',
                            placeholder: ic_blocks_js_global.strings.search_placeholder,
                            onChange: onChangeProductSearch
                        })
                    )
                ];
                var main_product_content = [];
                var sidebar_content = [];
                var product_selector_products = [];
                var main_product_content_override = [];
                var sidebar_content_override = [];
                if (Array.isArray(selectedProduct)) {
                    productOptions.forEach((product) => {
                        product_selector_products.push(
                            el(PanelRow, {key: subname + '-checkbox-row-product-' + product.value},
                                el(CheckboxControl, {
                                    label: product.label,
                                    checked: selectedProductIDs.includes(product.value),
                                    value: product.value,
                                    key: subname + '-checkbox-product-' + product.value,
                                    onChange: function (selected) {
                                        selectProduct(selected, product.value, product.label);
                                    }
                                })
                            )
                        );
                    });
                } else {
                    product_selector_products.push(el(RadioControl, {
                        label: '',
                        selected: selectedProduct,
                        key: subname + '-product',
                        onChange: selectProduct,
                        options: productOptions
                    }));
                    if (attributes.productFromContext !== undefined) {
                        const toggleHelp = attributes.productFromContext ? ic_blocks_js_global.strings.context_enabled_help : ic_blocks_js_global.strings.context_disabled_help;
                        const from_content_body = el(PanelBody, {
                                title: '',
                                key: subname + 'select-context-product-panel-body',
                                className: 'ic-panel-body',
                                initialOpen: true
                            },
                            el(PanelRow, {key: subname + '-panel-body-row-product-search-notice'},
                                el(ToggleControl, {
                                    label: ic_blocks_js_global.strings.get_product_from_context,
                                    checked: attributes.productFromContext,
                                    key: subname + '-product-from-context',
                                    help: toggleHelp,
                                    onChange: onChangeProductFromContext,
                                })
                            )
                        );
                        if (attributes.productFromContext) {
                            main_product_content_override.push(from_content_body);
                            sidebar_content_override.push(from_content_body);
                        } else {
                            main_product_content.push(from_content_body);
                            sidebar_content.push(from_content_body);
                        }
                    }
                }
                var products_label = ic_blocks_js_global.strings.select_product;
                if (attributes.category !== undefined) {
                    products_label = ic_blocks_js_global.strings.by_product;
                } else {
                    const block_type = blocks.getBlockType(props.name);
                    const block_title = block_type.title;
                    products_label = block_title + ' - ' + products_label;
                }
                var search_panel_body_classname = 'ic-panel-body';
                var found_products = 0;
                if (selected_products_query) {
                    found_products += selected_products_query.length;
                }
                if (products) {
                    found_products += products.length;
                }
                if (!productSearch && (found_products < 10 || found_products === 0)) {
                    search_panel_body_classname = 'ic-hidden-panel-body';
                }
                if (products === null || products.length === 0) {
                    if (block_data.ready) {
                        if (productSearch) {
                            product_selector_search.push(
                                el(PanelRow, {key: subname + '-panel-body-row-product-search-notice'},
                                    el(Notice, {
                                        key: subname + 'product-selector-not-found',
                                        status: 'error',
                                        isDismissible: false
                                    }, 'Nothing found. Please use a different keyword.')
                                )
                            );
                        }
                    } else {
                        product_selector_products.push(
                            el(LoadingControl, {
                                key: subname + 'product-selector-loading',
                            })
                        );
                    }
                }
                main_product_content.push(
                    el(PanelBody, {
                            title: ic_blocks_js_global.strings.search_product,
                            key: subname + '-search-products-panel-body',
                            className: search_panel_body_classname,
                            initialOpen: true
                        },
                        product_selector_search
                    )
                );
                main_product_content.push(
                    el(PanelBody, {
                            title: ic_blocks_js_global.strings.select_product,
                            key: subname + 'select-product-panel-body',
                            className: 'ic-panel-body',
                            initialOpen: true
                        },
                        product_selector_products
                    )
                );

                if (main_product_content_override.length) {
                    main_product_content = main_product_content_override;
                }
                var product_selector = el(Panel, {
                        header: products_label,
                        key: subname + '-search-products-block-panel'
                    },
                    main_product_content
                );
                sidebar_content.push(
                    product_selector_search
                );
                sidebar_content.push(product_selector_products);
                if (sidebar_content_override.length) {
                    sidebar_content = sidebar_content_override;
                }
                sidebar_additional.push(
                    el(PanelBody, {
                            title: ic_blocks_js_global.strings.select_product,
                            key: subname + '-side-search-products-panel-body',
                            className: 'ic-panel-body',
                            initialOpen: false
                        },
                        sidebar_content
                    )
                );
                if (((Array.isArray(selectedProduct) && selectedProduct.length === 0)/* || (!selectedProduct && props.context.postType !== 'al_product' && ic_blocks_js_global.context.type !== 'al_product')*/)) {
                    if (settings_content.length === 0) {
                        settings_open = true;
                    }
                } else {
                    settings_open = false;
                }
                settings_content.push(product_selector);
            }
            if (attributes.image_id !== undefined) {
                // var selected_image_id = attributes.image_id;
                var image_id_selector = [];
                const thumbnailUrl = window.wp.data.useSelect(select => {
                    const image = attributes.image_id && select('core').getMedia(attributes.image_id);
                    return image && image.media_details.sizes.medium.source_url;
                }, [attributes.image_id]);
                const mediaPreview = !!thumbnailUrl && el('img', {
                    //label: ic_blocks_js_global.strings.upload_image,
                    src: thumbnailUrl,
                });
                image_id_selector.push(
                    el(ImageControl, {
                        key: subname + 'image-id-selector',
                        //label: ic_blocks_js_global.strings.upload_image,
                        accept: 'image/*',
                        allowedTypes: ['image'],
                        multiple: false,
                        onSelect: onChangeImageID,
                        handleUpload: true,
                        mediaPreview: mediaPreview,
                    })
                );
                settings_content.push(
                    el(PanelBody, {
                            title: ic_blocks_js_global.strings.select_product,
                            key: subname + 'select-product-panel-body',
                            className: 'ic-panel-body',
                            initialOpen: true
                        },
                        el(PanelRow, {key: subname + '-panel-body-row-image_id'},
                            image_id_selector
                        )
                    )
                );
            }
            if (settings_content.length !== 0) {
                toolbar.push(el(ToolbarButton,
                    {
                        icon: 'edit',
                        key: subname + '-edit-button',
                        title: ic_blocks_js_global.strings.edit_block,
                        onClick: () => setIsEditing(!isEditing),
                        isActive: isEditing || settings_open
                    }
                ));
            }

            var ret = [el(
                BlockControls,
                {key: subname + '-controls'},
                toolbar
            )];
            var sidebar_container = [];
            if (sidebar.length) {
                sidebar_container.push(
                    el(PanelBody, {
                            title: ic_blocks_js_global.strings.options,
                            key: subname + '-main-panel-body',
                            className: 'main-panel-body',
                            initialOpen: true
                        },
                        sidebar
                    )
                );
            }
            if (sidebar_additional.length) {
                sidebar_container.push(
                    sidebar_additional
                );
            }
            if (sidebar_container.length) {

                ret.push(
                    el(InspectorControls, {key: 'ic-price-field-table-block-controls'},
                        sidebar_container
                    )
                );
            }
            if (isEditing || settings_open) {
                output = settings_content;
                settings_open = true;
                if (!isEditing) {
                    setIsEditing(true);
                }
            }

            var block_class = 'ic-block-output';
            if (settings_open) {
                block_class += '-edit';
            }
            var block_props = {
                key: subname + '-div',
                className: block_class,
            };
            if (!isEditing) {
                block_props.style = style;
                if (settings_content.length !== 0 && toolbar.length <= 1) {
                    block_props.onClick = () => setIsEditing(true);
                }
            }
            ret.push(
                el('div', useBlockProps(block_props),
                    output
                )
            );

            return ret;
        },

        save: function () {
            return null;
        }
    });

    function ic_get_block_data(attributes) {

        const data = window.wp.data.useSelect((select) => {
            const selector = select('core');
            var data = {};
            data.ready = true;
            if (attributes.selectedCategory !== undefined) {
                var selectedCategories = ic_get_block_selected_categories(attributes);
                var cat_search_attr = {
                    per_page: -1,
                    _fields: 'id,name,count',
                    orderby: 'term_group',
                };
                if (attributes.categorySearch !== undefined) {
                    var categorySearch = attributes.categorySearch;
                    cat_search_attr.per_page = 10;
                    if (categorySearch) {
                        cat_search_attr.search = categorySearch;
                        cat_search_attr.per_page = -1;
                    }
                }
                if (selectedCategories.length !== 0) {
                    var all_category_objects = true;
                    var selected_category_args = {
                        per_page: -1,
                        _fields: 'id,name,count',
                        orderby: 'include',
                        'include': selectedCategories.map(function (item) {
                            if (typeof item === 'object') {
                                return parseInt(item.value, 10);
                            } else {
                                all_category_objects = false;
                                return parseInt(item, 10);
                            }
                        })
                    };
                    data.selected_categories = selector.getEntityRecords('taxonomy', 'al_product-cat', selected_category_args);
                    if (data.selected_categories === null && all_category_objects) {
                        data.selected_categories = selectedCategories;
                    } else {
                        //cat_search_attr.exclude = selectedCategories;
                    }
                    if (data.selected_categories === null) {
                        data.ready = false;
                    } else if (data.selected_categories.length >= cat_search_attr.per_page && cat_search_attr.per_page > 0) {
                        cat_search_attr.per_page = cat_search_attr.per_page + data.selected_categories.length;
                    }
                }

                data.categories = selector.getEntityRecords('taxonomy', 'al_product-cat', cat_search_attr);
                if (data.categories === null) {
                    data.ready = false;
                }
            }
            if (attributes.productSearch !== undefined && attributes.selectedProduct !== undefined) {
                var selectedProduct = ic_get_block_selected_products(attributes);
                var productSearch = attributes.productSearch;
                var search_attr = {
                    per_page: 10,
                    _fields: 'id,title'
                };
                if ((Array.isArray(selectedProduct) && selectedProduct.length !== 0) || (!Array.isArray(selectedProduct) && selectedProduct)) {
                    var all_product_objects = true;
                    var selected_products_args = {
                        per_page: -1,
                        _fields: 'id,title',
                        orderby: 'include',
                        'include': Array.isArray(selectedProduct) ? selectedProduct.map(function (item) {
                            if (typeof item === 'object') {
                                return parseInt(item.value, 10);
                            } else {
                                all_product_objects = false;
                                return parseInt(item, 10);
                            }
                        }) : selectedProduct
                    };
                    data.selected_products = selector.getEntityRecords('postType', 'al_product', selected_products_args);
                    if (data.selected_products === null && all_product_objects) {
                        data.selected_products = selectedProduct;
                    } else {
                        //search_attr.exclude = selectedProduct;
                    }
                    if (data.selected_products === null) {
                        data.ready = false;
                    } else if (data.selected_products.length >= search_attr.per_page) {
                        search_attr.per_page = search_attr.per_page + data.selected_products.length;
                    }
                }
                if (productSearch) {
                    search_attr.search = productSearch;
                    search_attr.per_page = 100;
                }
                data.products = selector.getEntityRecords('postType', 'al_product', search_attr);
                if (data.products === null) {
                    data.ready = false;
                }
            }
            if (!data.ready) {
                for (const [key, value] of Object.entries(data)) {
                    data[key] = null;
                }
            }

            return data;
        });
        return data;
    }

    function ic_get_block_selected_products(attributes) {
        var selectedProduct = attributes.selectedProduct;
        if (Array.isArray(selectedProduct)) {
            if (selectedProduct.length) {
                selectedProduct = selectedProduct.map(function (item) {
                    if (typeof item === 'object') {
                        return {value: parseInt(item.value, 10), label: item.label};
                    } else {
                        return {value: parseInt(item, 10), label: 'Loading...'};
                    }
                });
            } else if (attributes.product !== undefined && attributes.product.length) {
                selectedProduct = attributes.product.map(function (item) {
                    return {value: parseInt(item, 10), label: 'Loading...'};
                });
            }
        } else {
            selectedProduct = parseInt(attributes.selectedProduct, 10);
        }
        return selectedProduct;
    }

    function ic_get_block_selected_categories(attributes) {
        var selectedCategory = attributes.selectedCategory;
        if (Array.isArray(selectedCategory)) {
            if (selectedCategory.length) {
                selectedCategory = selectedCategory.map(function (item) {
                    if (typeof item === 'object') {
                        return {value: parseInt(item.value, 10), label: item.label};
                    } else {
                        return {value: parseInt(item, 10), label: 'Loading...'};
                    }
                });
            } else if (attributes.category !== undefined && attributes.category.length) {
                selectedCategory = attributes.category.map(function (item) {
                    return {value: parseInt(item, 10), label: 'Loading...'};
                });
            }
        } else {
            selectedCategory = parseInt(attributes.selectedCategory, 10);
        }
        return selectedCategory;
    }

}

jQuery(window).on('load',
    function () {
        setTimeout(ic_block_meta_values_dynamic, 1000);
    }
);

function ic_block_meta_values_dynamic() {
    jQuery('.ic-product-meta-field').each(function () {
        jQuery(this).on('change', function () {
            var field_name = jQuery(this).attr('name');
            var meta_fields = {};
            meta_fields[field_name] = jQuery(this).val();
            wp.data.dispatch('core/editor').editPost({meta: meta_fields});
        });
    });
    wp.media.featuredImage.frame().on('select', function () {
        var thumb_id = wp.media.featuredImage.get();
        var field_name = '_thumbnail_id';
        var meta_fields = {};
        meta_fields[field_name] = thumb_id;
        wp.data.dispatch('core/editor').editPost({meta: meta_fields});
    });

    jQuery('#remove-post-thumbnail').on('click', function () {
        var field_name = '_thumbnail_id';
        var meta_fields = {};
        meta_fields[field_name] = '';
        wp.data.dispatch('core/editor').editPost({meta: meta_fields});
    });
}

