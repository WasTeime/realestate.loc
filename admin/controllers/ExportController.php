<?php

namespace admin\controllers;

use common\components\export\ExportJob;
use Yii;
use yii\web\{BadRequestHttpException, NotFoundHttpException, Response};

/**
 * Class ExportController
 *
 * @package admin\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ExportController extends AdminController
{
    /**
     * @throws NotFoundHttpException
     */
    public function actionDownload(string $filename): void
    {
        $path = Yii::getAlias('@root/admin/runtime/export');
        $file = $path . '/' . $filename;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = match ($extension) {
            'csv' => 'application/csv',
            default => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
        if (file_exists($file)) {
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header("Content-Type: $contentType; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            readfile($file);
            exit();
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionGetExportLog(string $id): ?array
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ExportJob::getProgressLog($id);
        }
        throw new BadRequestHttpException();
    }
}
