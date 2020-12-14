<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendProductsTable extends Migration
{
    public function up()
    {
        Schema::rename('tblProductData', 'products');
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('intProductDataId', 'id');
            $table->renameColumn('strProductName', 'name');
            $table->renameColumn('strProductDesc', 'description');
            $table->renameColumn('strProductCode', 'code');
            $table->renameColumn('dtmDiscontinued', 'discontinued_at');
            $table->renameColumn('dtmAdded', 'created_at');
            $table->renameColumn('stmTimestamp', 'updated_at');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('id', 'intProductDataId');
            $table->renameColumn('name', 'strProductName');
            $table->renameColumn('description', 'strProductDesc');
            $table->renameColumn('code', 'strProductCode');
            $table->renameColumn('discontinued_at', 'dtmDiscontinued');
            $table->renameColumn('created_at', 'dtmAdded');
            $table->renameColumn('updated_at', 'stmTimestamp');
        });
        Schema::rename('products', 'tblProductData');
    }
}
