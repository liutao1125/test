<?php
/**
*   表单元素类库
*
*  @author      Lin.x
*  @description 表单元素类库
*  @date        2015-5-27 15:38:52
*/
	class Tool_Element {
		/**
		 * 生成表单select
		 *
		 * @return string
		 * @author Lin.x
		 **/
		static public function select($name, $options=Array(), $value=0, $attr=Array()) {
            $attributes = '';
			foreach ($attr as $key=>$val) {
				$attributes .= "$key=\"$val\" ";
			}
			$html = "<select {$attributes}name=\"$name\">\n\t";
			if(!empty($options))
				foreach ($options as $key=>$val) {
					$html .= "\n\t<option value=\"$key\"".(($value == $key)?" selected=\"\"":null).">$val</option>";
				}
			$html .= "\n\t</select>\n";
			return $html;
		}

		/**
		 *		生成 radio checkbox

            Element::select('user', $options, 3, array(
				'id' => 'userSelect',
				'class' => 'from-select',
			)),

			Element::checkbox('user2', $options, 1, array(
				'id' => 'userSelect2',
				'class' => 'from-select',
			)),

			Element::checkbox('user21', $options, 2, array(
				'id' => 'userSelect21',
				'class' => 'from-select',
			), array(3)),

			Element::checkbox('user3', array(
				1 => '张三',
				2 => '李四',
				3 => '王麻子',
			), array(2, 3), array(
				'id' => 'userSelect3',
				'class' => 'from-select',
			), array(3))

		 *
		 *		@param $name string 表单属性 name
		 *		@param $options array 选项
		 *		@param $value array 选项值 array 则是checkbox 否则为radio
		 *		@param $attrs array 属性
		 *		@param $disabled array 选项禁用
		 *		@param $prefix 单项表单前缀 如果没有后缀 则以前缀为tag eg : p => <p></p>
		 *		@param $subfix 单项表单后缀
		 *		@return string
		 */
		static public function checkbox($name, $options = array(), $value = array(), $attrs = array(), $disabled = array(), $prefix = null, $subfix = null){
			if(empty($options))
				return null;
			if(!is_null($prefix) && is_null($subfix)){
				$subfix = "</$prefix>";
				$prefix = "<$prefix>";
			}

			$type = is_array($value) ? 'checkbox':'radio';
			if(!empty($attrs) && is_array($attrs)){
				foreach ($attrs as $key=>$val) {
					$attr .= sprintf(' %s="%s"', $key, $val);
				}
				$attr .= " ";
			}

			if(empty($value))
				$default = true;

			$checkbox = is_array($value) ? '[]' : null;
			foreach ($options as $k=>$val) {
				$class = $attrs['class'] ? $attrs['class'] : $name;
				$id = $attrs['id'] . ($k+1);

				$checked = ($checkbox ? in_array($k, $value) :
					($k == $value)) ? 'checked="" ' : null;

				$isdisabled = (
					!empty($disabled) &&
					is_array($disabled) &&
					in_array($k, $disabled)
					) ? 'disabled="" ' : 'value="'.$k.'" ';

				if(!$n && empty($value)){
					$n++;
					$default = 'checked="" ';
				}else{
					unset($default);
				}

				$html .= $prefix . "<label class=\"$class\" for=\"$id\">" . PHP_EOL .
					"<input type=\"$type\" class=\"$class\" name=\"$name$checkbox\" id=\"$id\" $default $isdisabled $checked/> $val" . PHP_EOL .
					"</label>" . $subfix . PHP_EOL;

			}
			return $html;
		}
	}
