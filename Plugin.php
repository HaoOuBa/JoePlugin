<?php

/**
 * Joe主题配套插件
 * 
 * @package JoePlugin
 * @author Joe
 * @version 1.0.5
 * @link https://78.al
 *
 */
class JoePlugin_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        /* 注入JS */
        Typecho_Plugin::factory('admin/footer.php')->end = array('JoePlugin_Plugin', 'assets');

        /* 注入开放API */
        Helper::addRoute('jsonp', '/api/[type]', 'JoePlugin_Action');
        Helper::addAction('json', 'JoePlugin_Action');

        /* 注入评论回调 */
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('JoePlugin_Plugin', 'send');
        Typecho_Plugin::factory('Widget_Comments_Edit')->finishComment = array('JoePlugin_Plugin', 'send');

        /* 注入文章发布回调 */
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('JoePlugin_Plugin', 'submit');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('JoePlugin_Plugin', 'submit');
    }

    public static function deactivate()
    {
        Helper::removeRoute('jsonp');
        Helper::removeAction('json');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $JSearchNum = new Typecho_Widget_Helper_Form_Element_Select(
            'JSearchNum',
            array(
                'off' => '关闭（默认）',
                '1' => '1条',
                '2' => '2条',
                '3' => '3条',
                '4' => '4条',
                '5' => '5条',
                '6' => '6条',
                '7' => '7条',
                '8' => '8条',
                '9' => '9条',
                '10' => '10条',
            ),
            'off',
            'QQ机器人 —— 搜索结果条数',
            '介绍：用于修改QQ群机器人显示的搜索条数 <br>
             API地址：域名/api/search?keyword= <br>
             使用教程：机器人插件直接与上方接口对接即可'
        );
        $form->addInput($JSearchNum->multiMode());

        $JCommentSend = new Typecho_Widget_Helper_Form_Element_Select(
            'JCommentSend',
            array(
                'off' => '关闭（默认）',
                'on' => '开启',
            ),
            'off',
            'QQ机器人 —— 评论推送QQ',
            '介绍：用于设置是否在根目录生成最新一条评论文件 <br>
             API地址：域名/robot/comment.txt <br>
             使用教程：机器人插件定时访问该API，如果文件内容有变动，则向QQ推送消息'
        );
        $form->addInput($JCommentSend->multiMode());

        $JPublishSend = new Typecho_Widget_Helper_Form_Element_Select(
            'JPublishSend',
            array(
                'off' => '关闭（默认）',
                'on' => '开启',
            ),
            'off',
            'QQ机器人 —— 发布文章推送QQ',
            '介绍：用于设置是否在根目录生成最新发布的文章相关信息 <br>
             API地址：域名/robot/publish.txt <br>
             使用教程：机器人插件定时访问该API，如果文件内容有变动，则向QQ推送消息'
        );
        $form->addInput($JPublishSend->multiMode());
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function assets()
    {
        echo '<script src="' . Helper::options()->pluginUrl . '/JoePlugin/assets/js/config.min.js"></script>';
    }

    public static function send($comment)
    {
        $status = Helper::options()->plugin('JoePlugin')->JCommentSend;
        /* 用户评论时才进行推送 */
        if ($status === 'on' && $comment->authorId != $comment->ownerId) {
            $title = $comment->title;
            $author = $comment->author;
            $text = preg_replace('/\{!\{([^\"]*)\}!\}/', '# 图片回复', $comment->text);
            $time = date('Y年m月d日 H:i:s', $comment->created);
            $link = substr($comment->permalink, 0, strrpos($comment->permalink, "#"));
            $txt = "您收到一条新的评论！\n\n文章：{$title}\n昵称：{$author}\n内容：{$text}\n链接：{$link}\n\n{$time}";
			$dir = 'robot/';
			$file = $dir . 'comment.txt';
			if (!file_exists($dir)) mkdir($dir);
			$fs = fopen($file, 'w+');
			fwrite($fs, $txt);
			fclose($fs);
        }
    }

    public static function submit($contents, $edit)
    {
        $status = Helper::options()->plugin('JoePlugin')->JPublishSend;
        /* 发布时才进行写入操作 */
        if ($status === 'on' && $contents['visibility'] === 'publish') {
            $type = "发布";
            $title = $edit->title;
            $author = $edit->author->screenName;
            $time = date('Y年m月d日 H:i:s', $edit->created);
            $link = $edit->permalink;
            $commentsNum = $edit->commentsNum;
            $commentStr = "";
            if ($edit->created !== $edit->modified) {
                $type = "更新";
                $time = date('Y年m月d日 H:i:s', $edit->modified);
                $commentStr = "\n评论量：{$commentsNum}";
            }
			$txt = "博客{$type}了一篇文章！\n\n标题：{$title}\n作者：{$author}\n链接：{$link}{$commentStr}\n\n{$time}";
			$dir = 'robot/';
			$file = $dir . 'publish.txt';
			if (!file_exists($dir)) mkdir($dir);
			$fs = fopen($file, 'w+');
			fwrite($fs, $txt);
			fclose($fs);
        }
    }
}
