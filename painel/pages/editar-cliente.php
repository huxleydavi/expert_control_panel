<?php
	if(isset($_GET['id'])){
		$id = (int)$_GET['id'];
		$cliente = Painel::select('tb_admin.clientes','id = ?',array($id));
	}else{
		Painel::alert('erro','Você precisa passar o parametro ID.');
		die();
	}
?>
<div class="box-content">
	<h2><i class="fa fa-pencil"></i> Editar Cliente</h2>

	<form class="ajax" atualizar method="post" action="<?php echo INCLUDE_PATH_PAINEL ?>ajax/forms.php" enctype="multipart/form-data">

		<div class="form-group">
			<label>Nome:</label>
			<input type="text" name="nome" value="<?php echo $cliente['nome']; ?>">
		</div><!--form-group-->
		<div class="form-group">
			<label>E-mail:</label>
			<input type="text" name="email" value="<?php echo $cliente['email']; ?>">
		</div><!--form-group-->

		<div class="form-group">
			<label>Tipo:</label>
			<select name="tipo_cliente">
				<option <?php if($cliente['tipo'] == 'fisico') echo 'selected'; ?> value="fisico">Fisico</option>
				<option <?php if($cliente['tipo'] == 'juridico') echo 'selected'; ?> value="juridico">Jurídico</option>
			</select>
		</div><!--form-group-->

		<?php
			if($cliente['tipo'] == 'fisico'){
		?>

		<div ref="cpf" class="form-group">
			<label>CPF</label>
			<input type="text" name="cpf" value="<?php echo $cliente['cpf_cnpj']; ?>" />
		</div><!--form-group-->

		<div style="display: none;" ref="cnpj" class="form-group">
			<label>CNPJ</label>
			<input type="text" name="cnpj" />
		</div><!--form-group-->

		<?php }else{ ?>
		<div style="display: none;" ref="cpf" class="form-group">
			<label>CPF</label>
			<input type="text" name="cpf" />
		</div><!--form-group-->

		<div style="display: block;" ref="cnpj" class="form-group">
			<label>CNPJ</label>
			<input type="text" name="cnpj" value="<?php echo $cliente['cpf_cnpj']; ?>" />
		</div><!--form-group-->

		<?php } ?>
		
		<div class="form-group">
			<label>Imagem</label>
			<input type="file" name="imagem"/>
		</div><!--form-group-->

		<div class="form-group">
			<input type="hidden" name="imagem_original" value="<?php echo $cliente['imagem']; ?>">
		</div><!--form-group-->

		<div class="form-group">
			<input type="hidden" name="tipo_acao" value="atualizar_cliente">
		</div>

		<div class="form-group">
			<input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
		</div>

		<div class="form-group">
			<input type="submit" name="acao" value="Atualizar!">
		</div><!--form-group-->

	</form>


	<h2><i class="fa fa-pencil"></i> Adicionar Pagamentos</h2>

	<form method="post">

	<?php
		if(isset($_POST['acao'])){
			$cliente_id = $id;
			$nome = $_POST['nome_pagto'];
			//$valor = str_replace('.','',$_POST['valor']);
			//$valor = str_replace(',','.',$valor);
			$valor = $_POST['valor'];
			$intervalo = $_POST['intervalo'];
			$numero_parcelas = $_POST['parcelas'];
			$status = 0;
			$vencimentoOriginal = $_POST['vencimento'];

			if(strtotime($vencimentoOriginal) < strtotime(date('Y-m-d'))){
				Painel::alert('erro','Você selecionou uma data negativa!');
			}else{

			for($i = 0; $i < $numero_parcelas; $i++){
				$vencimento = strtotime($vencimentoOriginal) + (($i * $intervalo) *(60*60*24));
				$sql = MySql::conectar()->prepare("INSERT INTO `tb_admin.financeiro` VALUES (null,?,?,?,?,?)");
				$sql->execute(array($cliente_id,$nome,$valor,date('Y-m-d',$vencimento),0));
			}
			Painel::alert('sucesso','O(s) pagamento(s) foi inserido com sucesso!');
			}

		}

	?>

	<div class="form-group">
			<label>Nome do pagamento:</label>
			<input type="text" name="nome_pagto" />
	</div><!--form-group-->
		
	<div class="form-group">
			<label>Valor do pagamento:</label>
			<input type="text" name="valor" />
	</div><!--form-group-->
	<div class="form-group">
			<label>Número de parcelas:</label>
			<input type="text" name="parcelas" />
	</div><!--form-group-->
	<div class="form-group">
			<label>Intervalo:</label>
			<input type="text" name="intervalo" />
	</div><!--form-group-->
		<div class="form-group">
			<label>Vencimento:</label>
			<input type="text" name="vencimento" />
	</div><!--form-group-->
		<div class="form-group">
			<input type="submit" name="acao" value="Inserir Pagamento">
		</div><!--form-group-->
	</form>

	<?php
	if(isset($_GET['pago'])){
			$sql = MySql::conectar()->prepare("UPDATE `tb_admin.financeiro` SET status = 1 WHERE id = ?");
			$sql->execute(array($_GET['pago']));
			Painel::alert('sucesso','O pagamento foi quitado com sucesso!');
	}
	?>
	<h2><i class="fa fa-id-card-o"></i> Pagamentos Pendentes</h2>

	<div class="wraper-table">
	<table>
		<tr>
			<td>Nome do pagamento</td>
			<td>Cliente</td>
			<td>Valor</td>
			<td>Vencimento</td>
			<td>Enviar e-mail</td>
			<td>Marcar como pago</td>
		</tr>

		<?php
			$sql = MySql::conectar()->prepare("SELECT * FROM `tb_admin.financeiro` WHERE status = 0 AND cliente_id = $id ORDER BY vencimento ASC");
			$sql->execute();
			$pendentes = $sql->fetchAll();

			foreach ($pendentes as $key => $value) {
			$clienteNome = MySql::conectar()->prepare("SELECT `nome` FROM `tb_admin.clientes` WHERE id = $value[cliente_id]");
			$clienteNome->execute();
			$clienteNome = $clienteNome->fetch()['nome'];
			$style="";
			if(strtotime(date('Y-m-d')) >= strtotime($value['vencimento'])){
				$style = 'style="background-color:#ff7070;font-weight:bold;"';
			}
		?>
		<tr <?php echo $style; ?>>
			<td><?php echo $value['nome']; ?></td>
			<td><?php echo $clienteNome; ?></td>
			<td><?php echo $value['valor']; ?></td>
			<td><?php echo date('d/m/Y',strtotime($value['vencimento'])); ?></td>
			<td><a class="btn edit" href="<?php echo INCLUDE_PATH_PAINEL ?>"><i class="fa fa-envelope" aria-hidden="true"></i> E-mail</a></td>
			<td><a style="background: #00bfa5;" class="btn" href="<?php echo INCLUDE_PATH_PAINEL ?>editar-cliente?id=<?php echo $id; ?>&pago=<?php echo $value['id'] ?>"><i class="fa fa-check"></i> Pago</a></td>
		</tr>

		<?php } ?>

		

	</table>
	</div>

	<h2><i class="fa fa-id-card-o"></i> Pagamentos Concluidos</h2>

	<div class="wraper-table">
	<table>
		<tr>
			<td>Nome do pagamento</td>
			<td>Cliente</td>
			<td>Valor</td>
			<td>Vencimento</td>
		</tr>

		<?php
			$sql = MySql::conectar()->prepare("SELECT * FROM `tb_admin.financeiro` WHERE status = 1 AND cliente_id=$id ORDER BY vencimento ASC LIMIT 10");
			$sql->execute();
			$pendentes = $sql->fetchAll();

			foreach ($pendentes as $key => $value) {
			$clienteNome = MySql::conectar()->prepare("SELECT `nome` FROM `tb_admin.clientes` WHERE id = $value[cliente_id]");
			$clienteNome->execute();
			$clienteNome = $clienteNome->fetch()['nome'];
		?>
		<tr>
			<td><?php echo $value['nome']; ?></td>
			<td><?php echo $clienteNome; ?></td>
			<td><?php echo $value['valor']; ?></td>
			<td><?php echo date('d/m/Y',strtotime($value['vencimento'])); ?></td>
		</tr>

		<?php } ?>

	</table>
	</div>

</div><!--box-content-->