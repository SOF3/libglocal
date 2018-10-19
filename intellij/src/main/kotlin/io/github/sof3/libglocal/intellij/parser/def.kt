package io.github.sof3.libglocal.intellij.parser

import com.intellij.lang.ASTNode
import com.intellij.lang.ParserDefinition
import com.intellij.lang.ParserDefinition.SpaceRequirements
import com.intellij.openapi.project.Project
import com.intellij.psi.FileViewProvider
import com.intellij.psi.PsiElement
import com.intellij.psi.tree.IStubFileElementType
import com.intellij.psi.tree.TokenSet
import io.github.sof3.libglocal.intellij.LibglocalLanguage
import io.github.sof3.libglocal.intellij.psi.LgcFile
import io.github.sof3.libglocal.intellij.psi.LgcFileStub

/*
 * libglocal
 *
 * Copyright 2018 SOFe
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class LibglocalParserDef : ParserDefinition {
	override fun createParser(p: Project?) = LgcParser()

	override fun createLexer(p: Project?) = LibglocalLexer().wrapper

	override fun getFileNodeType() = LgcFileElementType()

	override fun createFile(vp: FileViewProvider) = LgcFile(vp)

	override fun createElement(node: ASTNode): PsiElement = LgcElements.Factory.createElement(node)

	override fun getStringLiteralElements() = TokenSet.create(
		LgcElements.LITERAL_STRING,
		LgcElements.LITERAL_ESCAPE,
		LgcElements.CONT_SPACE,
		LgcElements.CONT_NEWLINE,
		LgcElements.CONT_CONCAT
	)

	override fun getCommentTokens(): TokenSet = TokenSet.create(LgcElements.COMMENT)

	override fun spaceExistenceTypeBetweenTokens(left: ASTNode?, right: ASTNode?): SpaceRequirements {
		if(right == LgcElements.MISSING_WHITESPACE) return SpaceRequirements.MUST
		return SpaceRequirements.MAY
	}
}

class LgcFileElementType : IStubFileElementType<LgcFileStub>(LibglocalLanguage)
