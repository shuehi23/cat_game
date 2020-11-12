$(function () {
    //================================
    // 攻撃時のエフェクト
    //================================
    $jsScratchBtn = $('.js-scratch-btn');
    $jsBeamBtn = $('.js-beam-btn');
    $jsScratchWound = $('.js-scratch-wound');
    $jsBeamWound = $('.js-beam-wound');
    // 爪攻撃
    if ($jsScratchBtn.hasClass('approach')) {
        $jsScratchBtn.on('click', function () {
            console.log('scratch_clicked');
            $jsScratchWound.show();
        })
    }
    //　ビーム攻撃
    if (!$jsBeamBtn.hasClass('emptybeam')) {
        $jsBeamBtn.on('click', function () {
            console.log('beam_clicked');
            $jsBeamWound.show();
        })
    }
    // フッターを下部に固定
    var $ftr = $('#footer');
    if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
        $ftr.attr({ 'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
    }
});
