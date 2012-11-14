<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Akatus
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Akatus_Akatus_RetornoController
    extends Mage_Core_Controller_Front_Action
{

    //recebe o retorno da Akatus em /akatus/retorno
    public function indexAction(){
            #abre conexao com o banco de dados
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
           
            #inicializacao de variaveis
        $orderId=0; 
        $CodigoTransacao = $_POST["transacao_id"];
        $StatusTransacao = $_POST["status"];
        $tokenRecebido   = $_POST["token"];
        
        $StatusNovo="";

            
        #resgata o numero da order atrelado a transacao
        $retorno=$db->query("SELECT idpedido FROM akatus_transacoes WHERE codtransacao = '".$CodigoTransacao."' ORDER BY id DESC");
        
        
           $i =0;
        while ($row = $retorno->fetch()){
            
            echo ($i+1).")".$row['idpedido']. "<br />";
            $orderId = $row['idpedido'];
            $i++;
        }

    /*

        $retorno2=$db->query("SELECT * FROM akatus_transacoes");
        
        
           $i =0;
        while ($row = $retorno2->fetch()){
            
            echo ($i+1).") idpedido:".$row['idpedido']. " id:".$row["id"]." codtransacao:".$row["codtransacao"]."<br />";
            //$orderId = $row['idpedido'];
            $i++;
        }


        */
           echo "OrderId obtido:" .$orderId;
        
        echo 'tentemos pegar o token NIP';    
        #faz a conferencia da transacao
        $tokennip=Mage::getStoreConfig('payment/akatus/tokennip');
        
        echo 'tokenip resgatado';
        
        //validao retorno
        if($tokennip == $tokenRecebido){
                echo "Tokenip OK!";
           
            /*
                * Altera o Status do Pedido
                STATE_NEW             = 'new';              STATE_PENDING_PAYMENT = 'pending_payment';
                STATE_PROCESSING      = 'processing';       STATE_COMPLETE        = 'complete';
                STATE_CLOSED          = 'closed';           STATE_CANCELED        = 'canceled';
                STATE_HOLDED          = 'holded';           STATE_PAYMENT_REVIEW  = 'payment_review';
            */  
                
               /// echo "Alterando Status da transacao para inserir no BD do magento. Entra: ".$StatusTransacao. " deve ser salvo ";
                
            switch ($StatusTransacao) {
                case "Aguardando Pagamento":
                    $StatusNovo="pending_payment";
                    break;
                case "Aprovado":
                    $StatusNovo="complete";
                    break;
                case "Em Análise":
                    $StatusNovo="pending_payment";
                    break;
                case "Cancelado":
                    $StatusNovo="canceled";
                    break;
                case "Devolvido":
                    $StatusNovo="canceled";
                    break;
                case "Completo":
                    $StatusNovo="complete";
                    break;                        
                default: 
                   $StatusNovo="processing";    
            } 
            echo $StatusNovo . ".";
            #altera status do pedido
            $order = Mage::getModel('sales/order')->load($orderId);
                        echo 'getModel!';   
            //        print_r($order);
            $order->setStatus($StatusNovo);
              echo 'setStatus!';     
            $order->save();
             echo 'saved!';
                   
        } else {
                echo 'tokenip fumaça!!!';
        }
    }
}
