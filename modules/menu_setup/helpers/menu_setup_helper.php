<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');

function app_admin_sidebar_custom_options($items)
{
    return _apply_menu_items_options($items, json_decode(get_option('aside_menu_active')));
}

function app_admin_sidebar_custom_positions($items)
{
    return _apply_menu_items_position($items, json_decode(get_option('aside_menu_active')));
}

function app_admin_setup_menu_custom_options($items)
{
    return _apply_menu_items_options($items, json_decode(get_option('setup_menu_active')));
}

function app_admin_setup_menu_custom_positions($items)
{
    return _apply_menu_items_position($items, json_decode(get_option('setup_menu_active')));
}

function _apply_menu_items_options($items, $options)
{
    foreach ($items as $key => $item) {
        if (isset($options->{$item['slug']})) {
            if (isset($options->{$item['slug']}->disabled)
                && $options->{$item['slug']}->disabled === 'true') {
                // HRMS must stay reachable (attendance/leave routes live under timesheets).
                if (($item['slug'] ?? '') !== 'timesheets') {
                    unset($items[$key]);
                }
            } else {
                // Main item has custom icon
                if (isset($options->{$item['slug']}->icon) && $options->{$item['slug']}->icon === false) {
                    // False is when user set the icon empty from the builder
                    $items[$key]['icon'] = '';
                } elseif (!empty($options->{$item['slug']}->icon)) {
                    $items[$key]['icon'] = $options->{$item['slug']}->icon;
                }
            }

            if (isset($items[$key]['children']) && is_array($items[$key]['children'])) {
                $items[$key]['children'] = _apply_menu_items_children_options(
                    $items[$key]['children'],
                    $options->{$item['slug']}->children ?? null
                );
            }
        }
    }

    return $items;
}

/**
 * Apply Menu Setup disabled/icon flags to nested sidebar children (HRMS 3-level menus).
 *
 * @param array<int, array<string, mixed>> $children
 * @param object|null $options_children
 * @return array<int, array<string, mixed>>
 */
function _apply_menu_items_children_options(array $children, $options_children)
{
    if ($options_children === null) {
        return $children;
    }

    foreach ($children as $childKey => $child) {
        $slug = $child['slug'] ?? '';
        if ($slug === '' || !isset($options_children->{$slug})) {
            if (!empty($child['children'])) {
                $children[$childKey]['children'] = _apply_menu_items_children_options(
                    $child['children'],
                    $options_children
                );
            }
            continue;
        }

        $node = $options_children->{$slug};
        if (isset($node->disabled) && $node->disabled === 'true') {
            unset($children[$childKey]);
            continue;
        }

        if (isset($node->icon) && $node->icon === false) {
            $children[$childKey]['icon'] = '';
        } elseif (!empty($node->icon)) {
            $children[$childKey]['icon'] = $node->icon;
        }

        if (!empty($child['children'])) {
            $children[$childKey]['children'] = _apply_menu_items_children_options(
                $child['children'],
                $node->children ?? null
            );
        }
    }

    return array_values($children);
}

/**
 * Keep Menu Setup order but merge in new default submenu items (e.g. HRMS nested links).
 *
 * @param array<int, array<string, mixed>> $ordered
 * @param array<int, array<string, mixed>> $defaults
 * @return array<int, array<string, mixed>>
 */
function _menu_merge_menu_children_lists(array $ordered, array $defaults)
{
    $indexBySlug = [];
    foreach ($ordered as $i => $child) {
        $slug = $child['slug'] ?? '';
        if ($slug !== '') {
            $indexBySlug[$slug] = $i;
        }
    }

    foreach ($defaults as $defaultChild) {
        $slug = $defaultChild['slug'] ?? '';
        if ($slug === '') {
            continue;
        }

        if (!isset($indexBySlug[$slug])) {
            $ordered[] = $defaultChild;
            $indexBySlug[$slug] = count($ordered) - 1;
            continue;
        }

        $i = $indexBySlug[$slug];
        $existingChildren = $ordered[$i]['children'] ?? [];
        $defaultChildren = $defaultChild['children'] ?? [];
        if (!empty($defaultChildren)) {
            $ordered[$i]['children'] = _menu_merge_menu_children_lists($existingChildren, $defaultChildren);
        }
    }

    return array_values($ordered);
}

function _apply_menu_items_position($items, $options)
{
    if (!is_array($options)) {
        $CI = &get_instance();
        // Has applied options
        $newItems          = [];
        $newItemsAddedKeys = [];

        foreach ($options as $key => $item) {
            // Check if the item is found because can be removed
            if ($newItem = $CI->app_menu->filter_item($items, $item->id)) {
                $newItems[$key]      = $newItem;
                $newItemsAddedKeys[] = $key;

                $newItems[$key]['children'] = [];

                if (isset($item->children)) {
                    foreach ($item->children as $child) {
                        if ($newChildItem = $CI->app_menu->filter_item($items, $child->id)) {
                            $newItems[$key]['children'][] = $newChildItem;
                            $newItemsAddedKeys[]          = $newChildItem['slug'];
                        }
                    }
                }

                if (isset($items[$key]['children']) && is_array($items[$key]['children'])) {
                    $newItems[$key]['children'] = _menu_merge_menu_children_lists(
                        $newItems[$key]['children'],
                        $items[$key]['children']
                    );
                }
            }
        }

        // Let's check if item is missed from $items to $newItems
        foreach ($items as $key => $item) {
            if (!in_array($key, $newItemsAddedKeys)) {
                $newItems[$key] = $item;
            }

            if (isset($item['collapse'])) {
                foreach ($item['children'] as $childKey => $child) {
                    if (!in_array($child['slug'], $newItemsAddedKeys)) {
                        $newItems[$key]['children'][] = $child;
                    }
                }

                $newItems[$key]['children'] = Arr::uniqueByKey($newItems[$key]['children'], 'slug');
            }
        }

        $items = $newItems;
    }

    // Finally apply the positions
    foreach ($items as $key => $item) {
        if (isset($options->{$item['slug']})) {
            $items[$key]['position'] = (int) $options->{$item['slug']}->position;

            foreach ($item['children'] as $childKey => $child) {
                if (isset($options->{$item['slug']}->children->{$child['slug']})) {
                    $items[$key]['children'][$childKey]['position'] = (int) $options->{$item['slug']}->children->{$child['slug']}->position;
                }
            }
        }
    }

    return $items;
}

function _menu_options_filter_child($menu_options, $slug)
{
    foreach ($menu_options as $option) {
        if (isset($option->children)) {
            foreach ($option->children as $childKey => $child) {
                if ($child->id == $slug) {
                    return $child;
                }
            }
        }
    }

    return false;
}

function app_get_menu_setup_icon($menu_options, $slug, $group)
{
    $child = _menu_options_filter_child($menu_options, $slug);

    // No options applied
    if (!isset($menu_options->{$slug}) && $child === false) {
        return get_instance()->app_menu->get_initial_icon($slug, $group);
    }

    // Icon is set empty by user on parent item
    if (isset($menu_options->{$slug})
        && $menu_options->{$slug}->icon === false) {
        return '';
    }

    // Icon is set empty by user on child item
    if ($child !== false && $child->icon === false) {
        return '';
    }

    // no icon applied, get the initial icon
    if (isset($menu_options->{$slug}) && $menu_options->{$slug}->icon === '') {
        return get_instance()->app_menu->get_initial_icon($slug, $group);
    } elseif (isset($menu_options->{$slug})) {
        // Custom icon is set on parent
        return $menu_options->{$slug}->icon;
    }
    // no icon applied, get the initial icon
    if ($child && $child->icon === '') {
        return get_instance()->app_menu->get_initial_icon($slug, $group);
    } elseif ($child) {
        // Custom icon is set on child
        return $child->icon;
    }

    return '';
}
