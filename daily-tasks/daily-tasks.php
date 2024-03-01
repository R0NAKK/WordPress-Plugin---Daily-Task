<?php
/*
Plugin Name: Daily Tasks
Description: Adds a Daily Tasks window to the WordPress dashboard.
Version: 1.0
Author: Your Name
*/

// Create custom post type for daily tasks
function create_daily_tasks_post_type() {
    register_post_type( 'daily_task',
        array(
            'labels' => array(
                'name' => __( 'Daily Tasks' ),
                'singular_name' => __( 'Daily Task' )
            ),
            'public' => false,
            'show_ui' => true,
            'menu_position' => 20,
            'supports' => array( 'title', 'editor', 'custom-fields' )
        )
    );
}
add_action( 'init', 'create_daily_tasks_post_type' );

// Add Daily Tasks widget to the dashboard
function add_daily_tasks_dashboard_widget() {
    wp_add_dashboard_widget(
        'daily_tasks_widget',
        'Daily Tasks',
        'display_daily_tasks_dashboard_widget'
    );
}
add_action( 'wp_dashboard_setup', 'add_daily_tasks_dashboard_widget' );

// Display Daily Tasks widget content
function display_daily_tasks_dashboard_widget() {
    $tasks = get_posts( array(
        'post_type' => 'daily_task',
        'posts_per_page' => -1,
    ) );

    if ( $tasks ) {
        echo '<ul>';
        foreach ( $tasks as $task ) {
            $task_id = $task->ID;
            $task_title = $task->post_title;
            $task_content = $task->post_content;
            $task_status = get_post_meta( $task_id, 'task_status', true );
            echo '<li>';
            echo '<strong>' . esc_html( $task_title ) . '</strong><br>';
            echo wpautop( esc_html( $task_content ) );
            echo '<span>Status: ' . esc_html( $task_status ) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo 'No tasks found.';
    }
}

// Add custom meta box for task status dropdown
function add_task_status_meta_box() {
    add_meta_box(
        'task_status_meta_box',
        'Task Status',
        'render_task_status_meta_box',
        'daily_task',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'add_task_status_meta_box' );

// Render task status dropdown
function render_task_status_meta_box( $post ) {
    $task_status = get_post_meta( $post->ID, 'task_status', true );
    ?>
<label for="task_status">Status:</label>
<select name="task_status" id="task_status">
    <option value="active" <?php selected( $task_status, 'active' ); ?>>Active</option>
    <option value="pending" <?php selected( $task_status, 'pending' ); ?>>Pending</option>
    <option value="completed" <?php selected( $task_status, 'completed' ); ?>>Completed</option>
</select>
<?php
}

// Save task status when post is saved
function save_task_status( $post_id ) {
    if ( isset( $_POST['task_status'] ) ) {
        update_post_meta( $post_id, 'task_status', sanitize_text_field( $_POST['task_status'] ) );
    }
}
add_action( 'save_post_daily_task', 'save_task_status' );